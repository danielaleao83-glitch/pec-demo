<?php

declare(strict_types=1);

namespace App\Application\Services\Auth\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * =========================================================
 * 🚫 TOO MANY ATTEMPTS EXCEPTION
 * 🏥 e-SUS APS / DATASUS / RNDS
 * =========================================================
 *
 * ✔ Anti brute force
 * ✔ Anti flood
 * ✔ Segurança federal
 * ✔ Proteção distribuída
 * ✔ Observabilidade
 *
 * =========================================================
 */
final class TooManyAttemptsException
    extends LoginException
{
    public function __construct(
        int $retryAfter = 60,
        array $context = []
    ) {

        parent::__construct(

            message:
                'Muitas tentativas de autenticação.',

            status:
                Response
                    ::HTTP_TOO_MANY_REQUESTS,

            context: array_merge(
                [

                    'retry_after'
                        => $retryAfter,
                ],
                $context
            ),
        );
    }

    /**
     * =========================================================
     * ⏱ RETRY AFTER
     * =========================================================
     */
    public function retryAfter(): int
    {
        return (int) (
            $this->context()['retry_after']
                ?? 60
        );
    }
}