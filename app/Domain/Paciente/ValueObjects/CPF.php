<?php

declare(strict_types=1);

namespace App\Domain\Paciente\ValueObjects;

class CPF
{
    public function __construct(
        private string $value
    ) {}

    public function value(): string
    {
        return $this->value;
    }

    public function masked(): string
    {
        return substr($this->value, 0, 3)
            . '.***.***-'
            . substr($this->value, -2);
    }
}
