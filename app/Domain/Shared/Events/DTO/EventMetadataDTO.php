<?php

declare(strict_types=1);

namespace App\Domain\Shared\Events\DTO;

class EventMetadataDTO
{
    public function __construct(
        public readonly string $eventUuid,
        public readonly string $eventClass,
        public readonly string $correlationId,
        public readonly string $hash,
        public readonly string $timestamp,
    ) {}
}