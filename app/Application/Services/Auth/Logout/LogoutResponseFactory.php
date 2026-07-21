<?php

declare(strict_types=1);

namespace App\Application\Services\Auth\Logout;

/**
 * =========================================================
 * 🔐 LOGOUT RESPONSE FACTORY
 * =========================================================
 */
final class LogoutResponseFactory
{
    /**
     * =========================================================
     * 📦 RESPONSE
     * =========================================================
     */
    public function make(
        string $correlationId,
        string $hashIntegridade,
    ): array {

        return [

            'success'
                => true,

            'message'
                => 'Logout realizado.',

            'meta' => [

                'correlation_id'
                    => $correlationId,

                'hash_integridade'
                    => $hashIntegridade,

                'timestamp'
                    => now()
                        ->toIso8601String(),
            ]
        ];
    }
}