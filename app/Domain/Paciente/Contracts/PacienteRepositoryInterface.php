<?php

declare(strict_types=1);

namespace App\Domain\Paciente\Contracts;

use App\Domain\Paciente\Entities\Paciente;

interface PacienteRepositoryInterface
{
    public function save(
        Paciente $paciente
    ): void;

    public function findById(
        string $id
    ): ?Paciente;
}
