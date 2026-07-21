<?php

declare(strict_types=1);

namespace App\Domain\Paciente\ValueObjects;

use DateTimeImmutable;

class DataNascimento
{
    public function __construct(
        private DateTimeImmutable $value
    ) {}

    public function value(): DateTimeImmutable
    {
        return $this->value;
    }
}
