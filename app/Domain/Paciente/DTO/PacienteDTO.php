<?php

declare(strict_types=1);

namespace App\Domain\Paciente\DTO;

class PacienteDTO
{
    public function __construct(
        public readonly string $nome,
        public readonly string $cpf,
        public readonly string $cns,
    ) {}
}
