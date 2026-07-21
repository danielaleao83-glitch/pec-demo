<?php

declare(strict_types=1);

namespace App\Services\ESusService\SISAB\Exceptions;

class SisabIntegrityException extends SisabException
{
    public function __construct(
        string $message = 'Falha de integridade SISAB',
        array $context = [],
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            message: $message,
            context: $this->enrichContext($context),
            type: 'INTEGRITY_ERROR',
            previous: $previous
        );
    }

    private function enrichContext(array $context): array
    {
        return array_merge([
            'layer' => 'integrity',
            'severity' => 'critical',
            'module' => 'SisabIntegridade',
            // melhor deixar timestamp ser definido no logger, não na exception
        ], $context);
    }
}