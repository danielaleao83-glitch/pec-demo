<?php

declare(strict_types=1);

namespace App\Domain\Auditoria\ValueObjects;

/**
 * =========================================================
 * 🔐 HASH INTEGRIDADE
 * =========================================================
 */
final class HashIntegridade
{
    private function __construct(
        private readonly string $value
    ) {}

    public static function generate(
        array $payload
    ): self {

        return new self(
            hash(
                'sha512',
                json_encode(
                    $payload,
                    JSON_UNESCAPED_UNICODE
                )
            )
        );
    }

    public static function fromString(
        string $value
    ): self {

        if (
            strlen($value) < 64
        ) {
            throw new \InvalidArgumentException(
                'Hash inválido.'
            );
        }

        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }
}