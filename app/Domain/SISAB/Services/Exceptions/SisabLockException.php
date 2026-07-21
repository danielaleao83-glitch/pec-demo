<?php

declare(strict_types=1);

namespace App\Services\ESusService\SISAB\Exceptions;

class SisabLockException extends SisabException
{
    public function __construct(
        string $message = 'Falha no lock SISAB',
        array $context = [],
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            message: $message,
            context: $this->enrichContext($context),
            type: 'LOCK_ERROR',
            previous: $previous
        );
    }

    /**
     * 🔐 contexto operacional de concorrência
     */
    private function enrichContext(array $context): array
    {
        return array_merge([
            'layer' => 'lock',
            'severity' => 'high',
            'module' => 'SisabLock',
            'system' => 'distributed_lock',
        ], $context);
    }
}