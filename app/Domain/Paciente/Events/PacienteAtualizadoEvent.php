<?php

declare(strict_types=1);

namespace App\Domain\Paciente\Events;

class PacienteAtualizadoEvent
{
    public function __construct(
        public readonly string $pacienteId
    ) {}
}
