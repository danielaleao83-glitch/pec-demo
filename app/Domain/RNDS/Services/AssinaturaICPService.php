<?php

namespace App\Services\ESusService\RNDS;

use App\Services\ESusService\RNDS\Vault\RndsCertificateVault;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class AssinaturaICPService
{
    /**
     * Tempo máximo do envelope (segundos)
     */
    protected int $ttl = 300;

    // =========================================================
    // 🔐 ASSINAR PAYLOAD (RNDS / e-SUS STYLE)
    // =========================================================
    public function assinar(array $payload): array
    {
        $traceId = (string) Str::uuid();

        $nonce = bin2hex(random_bytes(16));

        $timestamp = now()->timestamp;

        $envelope = [
            'trace_id' => $traceId,
            'nonce' => $nonce,
            'timestamp' => $timestamp,
            'payload' => $payload,
        ];

        $json = $this->canonicalizar($envelope);

        /**
         * 🚫 ANTI-REPLAY
         */
        if (Cache::has("rnds:nonce:{$nonce}")) {

            $this->security(
                'RNDS_REPLAY_BLOCKED',
                $traceId,
                ['nonce' => $nonce]
            );

            throw new RuntimeException('Replay detectado');
        }

        Cache::put(
            "rnds:nonce:{$nonce}",
            true,
            now()->addSeconds($this->ttl)
        );

        try {

            /**
             * 🔐 CERTIFICADO CENTRALIZADO
             */
            $cert = app(RndsCertificateVault::class)->load();

            $privateKey = openssl_pkey_get_private(
                $cert['pkey'],
                $cert['passphrase']
            );

            if (! $privateKey) {
                throw new RuntimeException('Falha chave privada ICP');
            }

            /**
             * ✍️ ASSINATURA
             */
            $signature = '';

            openssl_sign(
                $json,
                $signature,
                $privateKey,
                OPENSSL_ALGO_SHA256
            );

            openssl_free_key($privateKey);

            $hash = hash('sha256', $json);

            /**
             * 🧾 AUDITORIA
             */
            $this->audit('RNDS_SIGNATURE_CREATED', [
                'trace_id' => $traceId,
                'nonce' => $nonce,
                'hash' => $hash,
                'user_id' => auth()->id(),
                'ip' => request()->ip() ?? 'CLI',
            ]);

            return [
                'trace_id' => $traceId,

                'envelope' => $envelope,

                'signature' => base64_encode($signature),

                'hash' => $hash,

                'cert_hash' => $cert['cert_hash'],
            ];

        } catch (Throwable $e) {

            $this->security('RNDS_SIGNATURE_FAILURE', $traceId, [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    // =========================================================
    // 🔍 VERIFICAR ASSINATURA
    // =========================================================
    public function verificar(
        array $envelope,
        string $signatureBase64
    ): bool {

        $traceId = $envelope['trace_id'] ?? 'unknown';

        /**
         * 🚫 VALIDA TIMESTAMP
         */
        if (! isset($envelope['timestamp'])) {

            $this->security('RNDS_TIMESTAMP_MISSING', $traceId);

            return false;
        }

        if (
            now()->timestamp - $envelope['timestamp']
            > $this->ttl
        ) {

            $this->security('RNDS_ENVELOPE_EXPIRED', $traceId);

            return false;
        }

        /**
         * 🚫 ANTI-REPLAY
         */
        $nonce = $envelope['nonce'] ?? null;

        if (! $nonce) {

            $this->security('RNDS_NONCE_MISSING', $traceId);

            return false;
        }

        if (Cache::has("rnds:used:{$nonce}")) {

            $this->security('RNDS_REPLAY_VERIFY_BLOCK', $traceId, [
                'nonce' => $nonce,
            ]);

            return false;
        }

        try {

            $json = $this->canonicalizar($envelope);

            /**
             * 🔐 CERTIFICADO CENTRALIZADO
             */
            $cert = app(RndsCertificateVault::class)->load();

            $publicKey = openssl_pkey_get_public(
                $cert['cert']
            );

            if (! $publicKey) {
                throw new RuntimeException('Certificado inválido');
            }

            $valid = openssl_verify(
                $json,
                base64_decode($signatureBase64),
                $publicKey,
                OPENSSL_ALGO_SHA256
            ) === 1;

            /**
             * 🚫 MARCA NONCE COMO USADO
             */
            if ($valid) {

                Cache::put(
                    "rnds:used:{$nonce}",
                    true,
                    now()->addSeconds($this->ttl)
                );
            }

            /**
             * 🧾 AUDITORIA
             */
            $this->audit('RNDS_SIGNATURE_VERIFIED', [
                'trace_id' => $traceId,
                'nonce' => $nonce,
                'valid' => $valid,
                'ip' => request()->ip() ?? 'CLI',
            ]);

            return $valid;

        } catch (Throwable $e) {

            $this->security('RNDS_VERIFY_FAILURE', $traceId, [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    // =========================================================
    // 🧬 CANONICALIZAÇÃO
    // =========================================================
    protected function canonicalizar(array $payload): string
    {
        return json_encode(
            $this->sortRecursive($payload),
            JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
        );
    }

    protected function sortRecursive(array $data): array
    {
        ksort($data);

        foreach ($data as &$value) {

            if (is_array($value)) {
                $value = $this->sortRecursive($value);
            }
        }

        return $data;
    }

    // =========================================================
    // 🧾 AUDITORIA
    // =========================================================
    protected function audit(
        string $event,
        array $context
    ): void {

        Log::channel('audit')->info(
            $event,
            array_merge($context, [
                'timestamp' => now()->toIso8601String(),
            ])
        );
    }

    // =========================================================
    // 🚨 SEGURANÇA
    // =========================================================
    protected function security(
        string $event,
        string $traceId,
        array $context = []
    ): void {

        Log::channel('security')->critical(
            $event,
            array_merge($context, [
                'trace_id' => $traceId,
                'timestamp' => now()->toIso8601String(),
            ])
        );
    }
}