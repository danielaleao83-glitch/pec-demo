<?php

declare(strict_types=1);

namespace App\Application\Services\Auth\DTO;

/**
 * =========================================================
 * 🔐 LOGIN RESPONSE DTO
 * 🏥 e-SUS APS / DATASUS / RNDS
 * =========================================================
 *
 * ✔ Immutable
 * ✔ API Ready
 * ✔ Produção federal
 * ✔ Observabilidade
 * ✔ Integridade
 *
 * =========================================================
 */
final readonly class LoginResponseDTO
{
    public function __construct(
        public bool $success,
        public string $message,
        public array $user,
        public string $token,
        public string $sessionUuid,
        public string $correlationId,
        public string $hashIntegridade,
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

            'data' => [

                'user'
                    => $this->user,

                'token'
                    => $this->token,

                'session_uuid'
                    => $this->sessionUuid,
            ],

            'meta' => [

                'correlation_id'
                    => $this->correlationId,

                'hash_integridade'
                    => $this->hashIntegridade,

                'timestamp'
                    => $this->timestamp,
            ]
        ];
    }
}