<?php

declare(strict_types=1);

namespace App\Application\Services\Auth\Logout;

/**
 * =========================================================
 * 🔐 LOGOUT HASH SERVICE
 * =========================================================
 */
final class LogoutHashService
{
    /**
     * =========================================================
     * 🔐 GERA HASH FORENSE
     * =========================================================
     */
    public function generate(
        string $userId,
        string $correlationId,
    ): string {

        return hash(
            'sha512',
            implode('|', [

                $userId,

                $correlationId,

                config('app.key'),

                now()->timestamp,
            ])
        );
    }
}