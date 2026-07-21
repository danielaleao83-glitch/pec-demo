<?php

declare(strict_types=1);

namespace App\Domain\Paciente\Enums;

enum StatusPacienteEnum: string
{
    case ATIVO = 'ativo';
    case INATIVO = 'inativo';
}
