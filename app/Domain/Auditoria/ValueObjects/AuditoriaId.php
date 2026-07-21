<?php

namespace App\Domain\Auditoria\ValueObjects;

use Ramsey\Uuid\Uuid;

class AuditoriaId
{
    public function __construct(private string $value) {}

    public static function gerar(): self
    {
        return new self(Uuid::uuid7()->toString());
    }

    public function value(): string
    {
        return $this->value;
    }
}