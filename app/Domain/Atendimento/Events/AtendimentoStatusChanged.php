<<?php

declare(strict_types=1);

namespace App\Domain\Atendimento\Events;

use App\Domain\Atendimento\Enums\StatusAtendimento;
use App\Domain\Shared\Events\DomainEvent;

final class AtendimentoStatusChanged extends DomainEvent
{
    public const EVENT_NAME = 'atendimento.status_changed';
    public const EVENT_VERSION = 1;

    public function __construct(
        string $aggregateId,
        string $correlationId,

        public readonly StatusAtendimento $from,
        public readonly StatusAtendimento $to,

        public readonly ?string $userId = null,
        public readonly ?string $reason = null,

        /**
         * Evento que originou este evento
         * (rastreio distribuído)
         */
        public readonly ?string $causationId = null,
    ) {
        parent::__construct(
            aggregateId: $aggregateId,
            correlationId: $correlationId
        );
    }

    public function eventName(): string
    {
        return self::EVENT_NAME;
    }

    public function eventVersion(): int
    {
        return self::EVENT_VERSION;
    }

    public function payload(): array
    {
        return [

            'from' => $this->from->value,

            'to' => $this->to->value,

            'user_id' => $this->userId,

            'reason' => $this->reason,

            'causation_id' => $this->causationId,
        ];
    }
}