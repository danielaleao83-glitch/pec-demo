<?php

declare(strict_types=1);

namespace App\Domain\Auditoria\ValueObjects;

use Ramsey\Uuid\Uuid;

/**
 * =========================================================
 * 🔐 AUDIT ID
 * =========================================================
 */
final class AuditId
{
    private function __construct(
        private readonly string $value
    ) {}

    public static function generate(): self
    {
        return new self(
            Uuid::uuid7()->toString()
        );
    }

    public static function fromString(
        string $value
    ): self {

        if (
            ! Uuid::isValid($value)
        ) {
            throw new \InvalidArgumentException(
                'UUID auditoria inválido.'
            );
        }

        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }
}