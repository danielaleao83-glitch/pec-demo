<?php

declare(strict_types=1);

namespace App\Application\Services\Security;

use Illuminate\Http\Request;

/**
 * =========================================================
 * 🔐 REQUEST FINGERPRINT SERVICE
 * =========================================================
 *
 * ✔ Anti replay
 * ✔ Anti fraude
 * ✔ Integridade SHA512
 * ✔ LGPD
 * ✔ Segurança hospitalar
 *
 * =========================================================
 */
final class RequestFingerprintService
{
    public function generate(
        Request $request,
        array $payload = []
    ): string {

        return hash(
            'sha512',
            implode('|', [

                $request->ip(),

                substr(
                    (string) $request->userAgent(),
                    0,
                    500
                ),

                json_encode(
                    $payload,
                    JSON_UNESCAPED_UNICODE
                ),

                config('app.key'),
            ])
        );
    }
}