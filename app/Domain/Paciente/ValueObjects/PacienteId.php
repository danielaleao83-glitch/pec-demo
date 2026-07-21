<?php

declare(strict_types=1);

namespace App\Domain\Paciente\ValueObjects;

use Ramsey\Uuid\Uuid;

class PacienteId
{
    public function __construct(
        private string $value
    ) {

        if (!Uuid::isValid($value)) {
            throw new \InvalidArgumentException(
                'UUID inválido'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
