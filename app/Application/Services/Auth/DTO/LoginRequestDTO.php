<?php

declare(strict_types=1);

namespace App\Application\Services\Auth\DTO;

/**
 * =========================================================
 * 🔐 LOGIN REQUEST DTO
 * 🏥 e-SUS APS / DATASUS / RNDS
 * =========================================================
 *
 * ✔ Immutable
 * ✔ LGPD Ready
 * ✔ Sanitização
 * ✔ Segurança hospitalar
 * ✔ Blindagem produção
 *
 * =========================================================
 */
final readonly class LoginRequestDTO
{
    public function __construct(
        public string $email,
        public string $password,
        public ?string $ip = null,
        public ?string $userAgent = null,
        public ?string $correlationId = null,
    ) {}

    /**
     * =========================================================
     * 🧹 ARRAY
     * =========================================================
     */
    public function toArray(): array
    {
        return [

            'email'
                => strtolower(
                    trim($this->email)
                ),

            'password'
                => $this->password,

            'ip'
                => $this->ip,

            'user_agent'
                => $this->userAgent,

            'correlation_id'
                => $this->correlationId,
        ];
    }

    /**
     * =========================================================
     * 🔐 SERIALIZAÇÃO SEGURA
     * =========================================================
     */
    public function safe(): array
    {
        return [

            'email'
                => strtolower(
                    trim($this->email)
                ),

            'ip'
                => $this->ip,

            'correlation_id'
                => $this->correlationId,
        ];
    }
}