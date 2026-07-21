<?php

declare(strict_types=1);

namespace App\Services\ESusService\SISAB\Exceptions;

class SisabPayloadException extends SisabException
{
    public function __construct(
        string $message = 'Falha no payload SISAB',
        array $context = [],
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            message: $message,
            context: $this->enrichContext($context),
            type: 'PAYLOAD_ERROR',
            previous: $previous
        );
    }

    /**
     * 🧠 enriquecimento clínico do erro de payload
     */
    private function enrichContext(array $context): array
    {
        return array_merge([
            'layer' => 'payload',
            'severity' => 'medium',
            'module' => 'SisabPayload',
            'category' => 'data_validation_or_structure',
        ], $context);
    }
}