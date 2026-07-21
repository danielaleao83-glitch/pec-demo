<?php

declare(strict_types=1);

namespace App\Domain\Shared\Events\Traits;

trait HasDomainEvents
{
    private array $events = [];

    protected function record(object $event): void
    {
        $this->events[] = $event;
    }

    public function releaseEvents(): array
    {
        $events = $this->events;

        $this->events = [];

        return $events;
    }

    public function hasEvents(): bool
    {
        return !empty($this->events);
    }
}