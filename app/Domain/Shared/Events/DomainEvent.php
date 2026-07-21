<?php

declare(strict_types=1);

namespace App\Domain\Shared\Events;

use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

abstract class DomainEvent
{
    /**
     * Evento único global
     */
    public readonly string $eventId;

    /**
     * Momento exato do evento
     */
    public readonly DateTimeImmutable $occurredAt;

    /**
     * Versão do agregado
     */
    public readonly int $version;

    public function __construct(
        public readonly string $aggregateId,
        public readonly string $correlationId,
        int $version = 1,
    ) {
        $this->eventId = Uuid::uuid7()->toString();

        $this->occurredAt = new DateTimeImmutable();

        $this->version = $version;
    }

    abstract public function eventName(): string;

    abstract public function payload(): array;

    /**
     * Serialização determinística
     */
    final public function toArray(): array
    {
        return [

            'event_id' => $this->eventId,

            'event_name' => $this->eventName(),

            'aggregate_id' => $this->aggregateId,

            'correlation_id' => $this->correlationId,

            'version' => $this->version,

            'occurred_at' => $this->occurredAt->format(DATE_ATOM),

            'payload' => $this->payload(),
        ];
    }

    /**
     * Hash auditável
     */
    final public function hash(): string
    {
        $data = $this->toArray();

        ksort($data);

        return hash(
            'sha256',
            json_encode(
                $data,
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_THROW_ON_ERROR
            )
        );
    }

    /**
     * Aggregate helper
     */
    final public function aggregateId(): string
    {
        return $this->aggregateId;
    }

    /**
     * Correlation helper
     */
    final public function correlationId(): string
    {
        return $this->correlationId;
    }
}