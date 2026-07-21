<?php

declare(strict_types=1);

namespace App\Services\SISAB\Idempotency;

use App\Services\SISAB\Idempotency\Drivers\RedisSisabIdempotencyDriver;

class SisabIdempotencyStore
{
    public function __construct(
        private RedisSisabIdempotencyDriver $driver
    ) {}

    /**
     * Verifica se já existe evento processado ou em processamento
     */
    public function exists(string $fingerprint): bool
    {
        return $this->driver->exists($fingerprint);
    }

    /**
     * Reserva fingerprint de forma ATÔMICA (anti race condition)
     */
    public function reserve(string $fingerprint): void
    {
        $this->driver->reserve($fingerprint);
    }

    /**
     * Marca como concluído (evento finalizado)
     */
    public function complete(string $fingerprint): void
    {
        $this->driver->complete($fingerprint);
    }

    /**
     * Marca falha (mantém histórico para auditoria)
     */
    public function fail(string $fingerprint, string $reason): void
    {
        $this->driver->fail($fingerprint, $reason);
    }
}