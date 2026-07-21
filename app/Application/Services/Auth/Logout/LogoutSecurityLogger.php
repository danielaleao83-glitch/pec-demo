<?php

declare(strict_types=1);

namespace App\Application\Services\Auth\Logout;

use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * =========================================================
 * 🔐 LOGOUT SECURITY LOGGER
 * 🏥 e-SUS APS / DATASUS / RNDS
 * =========================================================
 *
 * ✔ Segurança hospitalar
 * ✔ Observabilidade
 * ✔ Logs forenses
 * ✔ Produção federal
 * ✔ Rastreamento distribuído
 *
 * =========================================================
 */
final class LogoutSecurityLogger
{
    /**
     * =========================================================
     * ✅ SUCCESS
     * =========================================================
     */
    public function success(
        array $context
    ): void {

        Log::channel('security')->info(
            'AUTH_LOGOUT_SUCCESS',
            $context
        );
    }

    /**
     * =========================================================
     * 🚨 FAILURE
     * =========================================================
     */
    public function failure(
        Throwable $exception,
        array $context = []
    ): void {

        Log::channel('security')->critical(
            'AUTH_LOGOUT_FAILURE',
            array_merge(
                [

                    'message'
                        => $exception
                            ->getMessage(),

                    'trace'
                        => substr(
                            $exception
                                ->getTraceAsString(),
                            0,
                            5000
                        ),
                ],
                $context
            )
        );
    }
}