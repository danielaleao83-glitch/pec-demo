<?php

namespace App\Services\Security;

use Illuminate\Support\Facades\Log;

class JwsService
{
    /**
     * 🚀 Assina payload no padrão JWS Compact (gov)
     */
    public static function assinar(array $payload, string $privateKeyPath, string $certPath, ?string $passphrase = null): string
    {
        try {

            // =========================================================
            // 🔐 HEADER PADRÃO GOVERNO
            // =========================================================
            $header = [
                'alg' => 'RS256',
                'typ' => 'JWT',
                'x5c' => [
                    self::certToBase64($certPath),
                ],
            ];

            // =========================================================
            // 📦 PAYLOAD
            // =========================================================
            $payloadJson = json_encode($payload, JSON_UNESCAPED_UNICODE);

            // =========================================================
            // 🔑 BASE64URL
            // =========================================================
            $base64Header = self::base64UrlEncode(json_encode($header));
            $base64Payload = self::base64UrlEncode($payloadJson);

            $data = $base64Header.'.'.$base64Payload;

            // =========================================================
            // 🔏 ASSINATURA RSA SHA256 (ICP-BRASIL)
            // =========================================================
            $privateKey = openssl_pkey_get_private(
                file_get_contents($privateKeyPath),
                $passphrase
            );

            if (! $privateKey) {
                throw new \Exception('Erro ao carregar chave privada');
            }

            openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256);

            openssl_free_key($privateKey);

            $base64Signature = self::base64UrlEncode($signature);

            return $data.'.'.$base64Signature;

        } catch (\Throwable $e) {

            Log::error('ERRO JWS', [
                'erro' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * 🔐 Converte certificado para base64 (x5c)
     */
    protected static function certToBase64(string $certPath): string
    {
        $cert = file_get_contents($certPath);

        $cert = str_replace([
            '-----BEGIN CERTIFICATE-----',
            '-----END CERTIFICATE-----',
            "\n", "\r",
        ], '', $cert);

        return trim($cert);
    }

    /**
     * 🔗 Base64URL encode
     */
    protected static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
