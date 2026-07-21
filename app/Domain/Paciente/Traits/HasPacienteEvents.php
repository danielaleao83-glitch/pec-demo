<?php

declare(strict_types=1);

namespace App\Domain\Paciente\Traits;

trait HasPacienteEvents
{
    private array $events = [];

    public function recordEvent(
        object $event
    ): void {

        $this->events[] = $event;
    }

    public function releaseEvents(): array
    {
        $events = $this->events;

        $this->events = [];

        return $events;
    }
}
