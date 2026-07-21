<?php

declare(strict_types=1);

namespace App\Domain\Shared\Events\ValueObjects;

class CorrelationId
{
    public function __construct(
        private string $value
    ) {}

    public function value(): string
    {
        return $this->value;
    }
}