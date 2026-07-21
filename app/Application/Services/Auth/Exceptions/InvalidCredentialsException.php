<?php

declare(strict_types=1);

namespace App\Application\Services\Auth\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * =========================================================
 * 🚫 INVALID CREDENTIALS EXCEPTION
 * 🏥 e-SUS APS / DATASUS / RNDS
 * =========================================================
 *
 * ✔ Anti brute force
 * ✔ Blindagem produção
 * ✔ Segurança hospitalar
 * ✔ Não expõe credenciais
 * ✔ LGPD
 *
 * =========================================================
 */
final class InvalidCredentialsException
    extends LoginException
{
    public function __construct(
        array $context = []
    ) {

        parent::__construct(

            message:
                'Credenciais inválidas.',

            status:
                Response
                    ::HTTP_UNAUTHORIZED,

            context:
                $context,
        );
    }
}