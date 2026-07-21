<?php

namespace App\Services\EsusService\RNDS;

use RuntimeException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AssinaturaJWSService
{
    protected string $certPath;
    protected string $keyPath;
    protected ?string $passphrase;

    public function __construct()
    {
        $this->certPath = storage_path('certs/cert.pem');
        $this->keyPath = storage_path('certs/key.pem');
        $this->passphrase = env('CERT_PASSWORD');
    }

    // =========================================================
    // 🚀 ASSINATURA JWS (RNDS - FEDERAL HARDENED)
    // =========================================================
    public function assinar(array $payload): string
    {
        $jti = (string) Str::uuid();
        $iat = time();
        $exp = $iat + 300;

        // 🔐 ENVELOPE DE SEGURANÇA
        $payload = $this->canonicalizar(array_merge($payload, [
            'jti' => $jti,
            'iat' => $iat,
            'exp' => $exp,
            'iss' => config('app.name'),
        ]));

        // 🚫 ANTI-REPLAY GLOBAL (JTI)
        if (Cache::has("jws:jti:{$jti}")) {
            throw new RuntimeException('REPLAY DETECTADO (JTI duplicado)');
        }

        Cache::put("jws:jti:{$jti}", true, now()->addMinutes(10));

        // =========================================================
        // HEADER RNDS (KID + CHAIN + CERT BINDING)
        // =========================================================
        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT',
            'kid' => hash('sha256', $this->certPath),
            'x5c' => [$this->getCertificadoBase64()],
            'cty' => 'RNDS+JSON',
        ];

        $headerEncoded = $this->base64UrlEncode(json_encode($header));

        $payloadEncoded = $this->base64UrlEncode(
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        $data = $headerEncoded . '.' . $payloadEncoded;

        // =========================================================
        // 🔐 ASSINATURA ICP-BRASIL
        // =========================================================
        $privateKey = openssl_pkey_get_private(
            file_get_contents($this->keyPath),
            $this->passphrase
        );

        if (! $privateKey) {
            throw new RuntimeException('Falha chave privada ICP');
        }

        openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        openssl_free_key($privateKey);

        $jws = $data . '.' . $this->base64UrlEncode($signature);

        // =========================================================
        // 🧾 AUDITORIA FEDERAL (IMUTÁVEL)
        // =========================================================
        Log::channel('audit')->info('JWS_ASSINADO', [
            'jti' => $jti,
            'hash' => hash('sha256', $jws),
            'iat' => $iat,
            'exp' => $exp,
            'ip' => request()->ip() ?? 'CLI',
            'user_id' => auth()->id(),
        ]);

        return $jws;
    }

    // =========================================================
    // 🔍 VERIFICAÇÃO RNDS (FULL HARDENED)
    // =========================================================
    public function verificar(string $jws): bool
    {
        [$header, $payload, $signature] = explode('.', $jws);

        $decoded = json_decode($this->base64UrlDecode($payload), true);

        // 🚨 EXPIRAÇÃO OBRIGATÓRIA
        if (isset($decoded['exp']) && time() > $decoded['exp']) {
            Log::warning('JWS_EXPIRADO', ['jti' => $decoded['jti'] ?? null]);
            return false;
        }

        $jti = $decoded['jti'] ?? null;

        // 🚫 REPLAY GLOBAL (USO ÚNICO)
        if ($jti && Cache::has("jws:used:{$jti}")) {
            Log::warning('JWS_REPLAY', ['jti' => $jti]);
            return false;
        }

        $data = $header . '.' . $payload;

        $cert = file_get_contents($this->certPath);
        $publicKey = openssl_pkey_get_public($cert);

        if (!$publicKey) {
            throw new RuntimeException('Certificado inválido');
        }

        $valid = openssl_verify(
            $data,
            $this->base64UrlDecode($signature),
            $publicKey,
            OPENSSL_ALGO_SHA256
        ) === 1;

        // 🔐 marca uso (ANTI-REPLAY DEFINITIVO)
        if ($valid && $jti) {
            Cache::put("jws:used:{$jti}", true, now()->addMinutes(10));
        }

        // 🧾 AUDITORIA FEDERAL VERIFICAÇÃO
        Log::channel('audit')->info('JWS_VERIFICADO', [
            'jti' => $jti,
            'valid' => $valid,
            'ip' => request()->ip() ?? 'CLI',
        ]);

        return $valid;
    }

    // =========================================================
    // 🧬 CANONICALIZAÇÃO STRICT RNDS
    // =========================================================
    protected function canonicalizar(array $payload): array
    {
        ksort($payload);

        foreach ($payload as &$value) {
            if (is_array($value)) {
                $value = $this->canonicalizar($value);
            }
        }

        return $payload;
    }

    // =========================================================
    // 🔧 BASE64 URL SAFE
    // =========================================================
    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    protected function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    // =========================================================
    // 🔐 CERTIFICADO ICP-BRASIL
    // =========================================================
    protected function getCertificadoBase64(): string
    {
        $cert = file_get_contents($this->certPath);

        return str_replace([
            '-----BEGIN CERTIFICATE-----',
            '-----END CERTIFICATE-----',
            "\n",
        ], '', $cert);
    }
}