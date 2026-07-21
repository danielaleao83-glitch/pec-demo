<?php

declare(strict_types=1);

namespace App\Application\Services\Security;

use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * =========================================================
 * 🔐 SECURITY RATE LIMIT SERVICE
 * =========================================================
 *
 * ✔ Anti flood
 * ✔ Anti brute force
 * ✔ Blindagem hospitalar
 * ✔ Produção federal
 *
 * =========================================================
 */
final class SecurityRateLimitService
{
    public function ensure(
        string $key,
        int $maxAttempts = 60,
        int $decaySeconds = 60
    ): void {

        $cacheKey =
            'security_rate_limit_' . sha1($key);

        $attempts = (int) Cache::get(
            $cacheKey,
            0
        );

        if ($attempts >= $maxAttempts) {

            abort(
                Response::HTTP_TOO_MANY_REQUESTS,
                'Rate limit excedido.'
            );
        }

        Cache::put(
            $cacheKey,
            $attempts + 1,
            $decaySeconds
        );
    }

    public function clear(
        string $key
    ): void {

        Cache::forget(
            'security_rate_limit_' . sha1($key)
        );
    }
}