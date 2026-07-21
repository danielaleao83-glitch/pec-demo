<?php

declare(strict_types=1);

namespace App\Domain\Paciente\ValueObjects;

class CNS
{
    public function __construct(
        private string $value
    ) {

        if (strlen($value) !== 15) {
            throw new \InvalidArgumentException(
                'CNS inválido'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
