<?php

declare(strict_types=1);

namespace App\Domain\Paciente\Events;

class PacienteCriadoEvent
{
    public function __construct(
        public readonly string $pacienteId,
        public readonly string $uuid,
    ) {}
}
