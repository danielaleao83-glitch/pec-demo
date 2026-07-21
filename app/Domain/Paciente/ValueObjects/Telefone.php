<?php

declare(strict_types=1);

namespace App\Domain\Paciente\ValueObjects;

class Telefone
{
    public function __construct(
        private string $value
    ) {}

    public function value(): string
    {
        return $this->value;
    }
}
