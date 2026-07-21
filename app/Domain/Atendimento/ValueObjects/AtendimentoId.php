<?php

declare(strict_types=1);

namespace App\Domain\Atendimento\ValueObjects;

use InvalidArgumentException;
use JsonSerializable;
use Ramsey\Uuid\Uuid;

/**
 * =========================================================
 * 🏥 ATENDIMENTO ID (FEDERAL GRADE)
 * =========================================================
 *
 * ✔ UUID v7
 * ✔ Imutabilidade real
 * ✔ Reconstrução segura
 * ✔ Replay-safe
 * ✔ Determinístico
 * ✔ Auditável
 * ✔ Zero-trust input
 * ✔ Compatível RNDS/e-SUS
 * ✔ Ledger-ready
 * ✔ Event-sourcing ready
 *
 * =========================================================
 */
final class AtendimentoId implements JsonSerializable
{
    /**
     * 🔐 Namespace do domínio
     */
    private const DOMAIN_NAMESPACE = 'ATENDIMENTO_ID';

    /**
     * 🔐 UUID puro
     */
    private readonly string $value;

    // =========================================================
    // 🔐 CONSTRUTOR PRIVADO
    // =========================================================
    private function __construct(
        string $value
    ) {
        $value = self::sanitize($value);

        self::ensureValid($value);

        $this->value = $value;
    }

    // =========================================================
    // 🧠 UUID v7 (ORDENÁVEL + DISTRIBUÍDO)
    // =========================================================
    public static function generate(): self
    {
        return new self(
            Uuid::uuid7()->toString()
        );
    }

    // =========================================================
    // 🔐 RECONSTRUÇÃO CONTROLADA
    // =========================================================
    public static function fromString(
        string $value
    ): self {
        return new self($value);
    }

    // =========================================================
    // 🌐 INPUT EXTERNO (SOAP/RNDS/e-SUS)
    // =========================================================
    public static function fromExternal(
        string $value
    ): self {
        return new self(
            self::sanitizeExternal($value)
        );
    }

    // =========================================================
    // 🔐 VALIDAÇÃO CENTRALIZADA
    // =========================================================
    private static function ensureValid(
        string $value
    ): void {

        if ($value === '') {
            throw new InvalidArgumentException(
                'AtendimentoId não pode ser vazio'
            );
        }

        if (!Uuid::isValid($value)) {
            throw new InvalidArgumentException(
                'AtendimentoId inválido'
            );
        }

        /**
         * 🔐 força UUID v7
         */
        $uuid = Uuid::fromString($value);

        if ($uuid->getVersion() !== 7) {
            throw new InvalidArgumentException(
                'AtendimentoId deve utilizar UUID v7'
            );
        }
    }

    // =========================================================
    // 🧹 SANITIZAÇÃO LOCAL
    // =========================================================
    private static function sanitize(
        string $value
    ): string {

        return strtolower(
            trim($value)
        );
    }

    // =========================================================
    // 🌐 SANITIZAÇÃO EXTERNA
    // =========================================================
    private static function sanitizeExternal(
        string $value
    ): string {

        $value = trim($value);

        /**
         * remove:
         * - caracteres invisíveis
         * - espaços estranhos
         * - lixo SOAP/XML
         */
        $value = preg_replace(
            '/[^a-fA-F0-9\-]/',
            '',
            $value
        );

        return strtolower($value);
    }

    // =========================================================
    // 📌 VALUE
    // =========================================================
    public function value(): string
    {
        return $this->value;
    }

    // =========================================================
    // 🔐 COMPARAÇÃO SEGURA
    // =========================================================
    public function equals(
        self $other
    ): bool {

        return hash_equals(
            $this->value,
            $other->value
        );
    }

    // =========================================================
    // 🔐 HASH ESTÁVEL
    // =========================================================
    public function hash(): string
    {
        return hash(
            'sha256',
            implode('|', [

                self::DOMAIN_NAMESPACE,

                $this->value,
            ])
        );
    }

    // =========================================================
    // 🔗 FINGERPRINT CURTO
    // =========================================================
    public function fingerprint(): string
    {
        return substr(
            $this->hash(),
            0,
            16
        );
    }

    // =========================================================
    // 🧾 SERIALIZAÇÃO SEGURA
    // =========================================================
    public function toArray(): array
    {
        return [

            'value' => $this->value,

            'hash' => $this->hash(),

            'fingerprint' => $this->fingerprint(),

            'version' => 7,
        ];
    }

    // =========================================================
    // 🔄 JSON SERIALIZABLE
    // =========================================================
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    // =========================================================
    // 🧠 NORMALIZAÇÃO
    // =========================================================
    public function normalize(): string
    {
        return $this->value;
    }

    // =========================================================
    // 🔁 STRING CAST
    // =========================================================
    public function __toString(): string
    {
        return $this->value;
    }
}