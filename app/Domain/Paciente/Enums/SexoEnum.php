<?php

declare(strict_types=1);

namespace App\Domain\Paciente\Enums;

enum SexoEnum: string
{
    case MASCULINO = 'M';
    case FEMININO = 'F';
    case OUTRO = 'O';
}
