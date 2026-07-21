<?php

declare(strict_types=1);

namespace App\Services\ESusService\SISAB\Exceptions;

class SisabReplayException extends SisabException
{
    public function __construct(
        string $message = 'Replay detectado no SISAB',
        array $context = [],
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            message: $message,
            context: $this->enrichContext($context),
            type: 'REPLAY_DETECTED',
            previous: $previous
        );
    }

    /**
     * 🧠 contexto de segurança temporal
     */
    private function enrichContext(array $context): array
    {
        return array_merge([
            'layer' => 'replay_protection',
            'severity' => 'high',
            'module' => 'SisabReplayGuard',
            'risk' => 'duplicate_request_or_resubmission',
        ], $context);
    }
}