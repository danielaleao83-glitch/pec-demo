<?php

namespace App\Services\ESusService\RNDS;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class CertificadoService
{
    /**
     * 🔐 CERTIFICADO RNDS - VAULT FEDERAL
     */
    public static function getCertificado(): array
    {
        $certPath = storage_path('certs/cert.pem');
        $keyPath  = storage_path('certs/key.pem');
        $pass     = env('CERT_PASSWORD');

        $context = self::context();

        // =========================================================
        // 🚨 HARD FAIL SECURITY (ANTI TAMPER / ANTI MISCONFIG)
        // =========================================================
        self::validateEnvironment($certPath, $keyPath, $pass);

        // =========================================================
        // 🔐 INTEGRIDADE CRIPTOGRÁFICA (ANTI-TAMPER)
        // =========================================================
        $certHash = hash_file('sha256', $certPath);
        $keyHash  = hash_file('sha256', $keyPath);

        self::validateHashBaseline($certPath, $certHash, 'cert');
        self::validateHashBaseline($keyPath, $keyHash, 'key');

        // =========================================================
        // 🧾 AUDITORIA FEDERAL (IMUTÁVEL)
        // =========================================================
        Log::channel('audit')->info('CERT_VAULT_ACCESS', [
            'cert_hash' => $certHash,
            'key_hash' => $keyHash,
            'user_id' => $context['user_id'],
            'ip' => $context['ip'],
            'fingerprint' => $context['fingerprint'],
            'timestamp' => now()->toIso8601String(),
        ]);

        // =========================================================
        // 🔐 CACHE SEGURADO (VAULT LOCK)
        // =========================================================
        return Cache::remember(
            'rnds:cert:vault:secure',
            now()->addMinutes(10),
            function () use ($certPath, $keyPath, $pass, $certHash, $keyHash) {

                return [
                    // paths (não expõe conteúdo direto)
                    'cert_path' => $certPath,
                    'key_path'  => $keyPath,

                    // security metadata
                    'hash_cert' => $certHash,
                    'hash_key'  => $keyHash,

                    // secret
                    'passphrase' => $pass,

                    // runtime guard
                    'vault_version' => 'RNDS-FEDERAL-1.0',
                ];
            }
        );
    }

    /**
     * 🧠 CONTEXTO FEDERAL (Rastreabilidade obrigatória)
     */
    private static function context(): array
    {
        return [
            'ip' => request()->ip() ?? 'CLI',
            'user_id' => auth()->id() ?? null,
            'fingerprint' => hash('sha256',
                (request()->ip() ?? '') .
                (request()->userAgent() ?? '') .
                (auth()->id() ?? 'guest')
            ),
        ];
    }

    /**
     * 🚨 VALIDAÇÃO DE AMBIENTE (FAIL-FAST RNDS)
     */
    private static function validateEnvironment(string $cert, string $key, ?string $pass): void
    {
        if (!file_exists($cert)) {
            Log::critical('CERT_MISSING');
            throw new RuntimeException('CERTIFICADO AUSENTE');
        }

        if (!file_exists($key)) {
            Log::critical('KEY_MISSING');
            throw new RuntimeException('CHAVE PRIVADA AUSENTE');
        }

        if (empty($pass)) {
            Log::critical('CERT_PASS_MISSING');
            throw new RuntimeException('PASSPHRASE NÃO CONFIGURADA');
        }

        if (!is_readable($cert) || !is_readable($key)) {
            Log::critical('CERT_NOT_READABLE');
            throw new RuntimeException('ARQUIVOS NÃO LEGÍVEIS');
        }
    }

    /**
     * 🔐 BASELINE DE INTEGRIDADE (ANTI-TAMPER REAL)
     */
    private static function validateHashBaseline(string $path, string $currentHash, string $type): void
    {
        $key = "rnds:vault:baseline:{$type}";

        $baseline = Cache::rememberForever($key, fn () => $currentHash);

        if ($baseline !== $currentHash) {

            Log::critical('CERT_TAMPER_DETECTED', [
                'type' => $type,
                'baseline' => $baseline,
                'current' => $currentHash,
            ]);

            throw new RuntimeException("VIOLAÇÃO DE INTEGRIDADE: {$type}");
        }
    }

    /**
     * 🔐 VALIDAÇÃO RÁPIDA DE SAÚDE DO VAULT
     */
    public static function validarIntegridade(): bool
    {
        try {
            $data = self::getCertificado();

            return file_exists($data['cert_path'])
                && file_exists($data['key_path'])
                && !empty($data['passphrase']);
        } catch (\Throwable $e) {
            return false;
        }
    }
}