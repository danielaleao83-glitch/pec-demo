<?php

declare(strict_types=1);

namespace App\Application\Services\Auth\DTO;

/**
 * =========================================================
 * 🔓 LOGOUT RESPONSE DTO
 * 🏥 e-SUS APS / DATASUS / RNDS
 * =========================================================
 *
 * ✔ Immutable
 * ✔ Segurança hospitalar
 * ✔ LGPD
 * ✔ Auditoria ready
 * ✔ Produção federal
 *
 * =========================================================
 */
final readonly class LogoutResponseDTO
{
    public function __construct(
        public bool $success,
        public string $message,
        public string $correlationId,
        public string $timestamp,
    ) {}

    /**
     * =========================================================
     * 📦 ARRAY
     * =========================================================
     */
    public function toArray(): array
    {
        return [

            'success'
                => $this->success,

            'message'
                => $this->message,

            'meta' => [

                'correlation_id'
                    => $this->correlationId,

                'timestamp'
                    => $this->timestamp,
            ]
        ];
    }
}