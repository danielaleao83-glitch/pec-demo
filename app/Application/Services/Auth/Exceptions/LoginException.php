<?php

declare(strict_types=1);

namespace App\Application\Services\Auth\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * =========================================================
 * 🔐 LOGIN EXCEPTION
 * 🏥 e-SUS APS / DATASUS / RNDS
 * =========================================================
 *
 * ✔ Base exception autenticação
 * ✔ Produção federal
 * ✔ LGPD Ready
 * ✔ Blindagem hospitalar
 * ✔ Segurança nacional
 *
 * =========================================================
 */
class LoginException extends Exception
{
    /**
     * 🔐 HTTP STATUS
     */
    protected int $status =
        Response::HTTP_UNAUTHORIZED;

    /**
     * 🔐 CONTEXTO
     */
    protected array $context = [];

    public function __construct(
        string $message = 'Falha autenticação.',
        int $status = Response::HTTP_UNAUTHORIZED,
        array $context = [],
        ?Throwable $previous = null,
    ) {

        parent::__construct(
            $message,
            $status,
            $previous
        );

        $this->status = $status;

        $this->context = $context;
    }

    /**
     * =========================================================
     * 🔐 STATUS
     * =========================================================
     */
    public function status(): int
    {
        return $this->status;
    }

    /**
     * =========================================================
     * 📦 CONTEXTO
     * =========================================================
     */
    public function context(): array
    {
        return $this->context;
    }

    /**
     * =========================================================
     * 📦 ARRAY
     * =========================================================
     */
    public function toArray(): array
    {
        return [

            'success'
                => false,

            'message'
                => $this->getMessage(),

            'status'
                => $this->status(),

            'context'
                => $this->context(),
        ];
    }
}