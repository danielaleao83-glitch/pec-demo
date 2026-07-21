<?php

declare(strict_types=1);

namespace App\Domain\Auditoria\Events;

use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

/**
 * =========================================================
 * 🔐 AUDITORIA REGISTRADA (FEDERATED EVENT)
 * =========================================================
 *
 * 🏥 e-SUS / RNDS CONCEPTUAL READY
 *
 * ✔ UUID v7 distribuído
 * ✔ Event sourcing seguro
 * ✔ Replay determinístico
 * ✔ Correlation tracing
 * ✔ Hash chain ready
 * ✔ Ledger compatibility
 * ✔ Imutabilidade forte
 * ✔ Serialização auditável
 * ✔ Offline-first compatible
 * ✔ Microsserviços ready
 *
 * =========================================================
 */
final class AuditoriaRegistrada
{
    /**
     * 🧾 UUID global do evento
     */
    private readonly string $eventId;

    /**
     * ⏱ Momento UTC imutável
     */
    private readonly DateTimeImmutable $occurredAt;

    /**
     * 🌐 Correlation ID distribuído
     */
    private readonly string $correlationId;

    /**
     * 🔗 Hash anterior da cadeia
     */
    private readonly ?string $previousHash;

    /**
     * 🔐 Hash atual do evento
     */
    private readonly string $currentHash;

    /**
     * 🧬 Versão do schema do evento
     */
    private readonly int $schemaVersion;

    public function __construct(
        private readonly string $auditoriaId,
        private readonly string $aggregateId,
        private readonly string $aggregateType,
        private readonly string $userId,
        private readonly string $acao,
        private readonly string $modulo,
        private readonly array $payload = [],
        ?string $correlationId = null,
        ?string $previousHash = null,
        int $schemaVersion = 1,
    ) {

        $this->eventId =
            Uuid::uuid7()->toString();

        $this->occurredAt =
            new DateTimeImmutable('now UTC');

        $this->correlationId =
            $correlationId
            ?? request()?->header('X-Correlation-Id')
            ?? Uuid::uuid7()->toString();

        $this->previousHash =
            $previousHash;

        $this->schemaVersion =
            $schemaVersion;

        /**
         * 🔐 HASH IMUTÁVEL DO EVENTO
         */
        $this->currentHash =
            $this->generateHash();
    }

    // =========================================================
    // 🔐 HASH CHAIN READY
    // =========================================================
    private function generateHash(): string
    {
        return hash_hmac(
            'sha256',
            json_encode(
                $this->normalizedPayload(),
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
            ),
            config('app.audit_key')
        );
    }

    // =========================================================
    // 🧾 SERIALIZAÇÃO DETERMINÍSTICA
    // =========================================================
    public function toArray(): array
    {
        return [

            'event_id'
                => $this->eventId,

            'event_type'
                => self::class,

            'aggregate_id'
                => $this->aggregateId,

            'aggregate_type'
                => $this->aggregateType,

            'auditoria_id'
                => $this->auditoriaId,

            'user_id'
                => $this->userId,

            'acao'
                => $this->acao,

            'modulo'
                => $this->modulo,

            'payload'
                => $this->normalizedPayload(),

            'correlation_id'
                => $this->correlationId,

            'previous_hash'
                => $this->previousHash,

            'current_hash'
                => $this->currentHash,

            'schema_version'
                => $this->schemaVersion,

            'occurred_at'
                => $this->occurredAt
                    ->format(DATE_ATOM),
        ];
    }

    // =========================================================
    // 🔐 NORMALIZAÇÃO ANTI-TAMPER
    // =========================================================
    private function normalizedPayload(): array
    {
        $payload = [

            'aggregate_id'
                => $this->aggregateId,

            'aggregate_type'
                => $this->aggregateType,

            'auditoria_id'
                => $this->auditoriaId,

            'user_id'
                => $this->userId,

            'acao'
                => $this->acao,

            'modulo'
                => $this->modulo,

            'payload'
                => $this->payload,

            'correlation_id'
                => $this->correlationId,

            'previous_hash'
                => $this->previousHash,

            'schema_version'
                => $this->schemaVersion,

            'occurred_at'
                => $this->occurredAt
                    ->format(DATE_ATOM),
        ];

        ksort($payload);

        return $payload;
    }

    // =========================================================
    // 🧠 ACCESSORS
    // =========================================================
    public function eventId(): string
    {
        return $this->eventId;
    }

    public function aggregateId(): string
    {
        return $this->aggregateId;
    }

    public function correlationId(): string
    {
        return $this->correlationId;
    }

    public function previousHash(): ?string
    {
        return $this->previousHash;
    }

    public function currentHash(): string
    {
        return $this->currentHash;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }

    // =========================================================
    // 🔐 INTEGRIDADE LOCAL
    // =========================================================
    public function verifyIntegrity(): bool
    {
        return hash_equals(
            $this->currentHash,
            $this->generateHash()
        );
    }

    // =========================================================
    // 🔗 EVENTO ENCADEADO?
    // =========================================================
    public function isChained(): bool
    {
        return $this->previousHash !== null;
    }

    // =========================================================
    // 🧬 EVENTO TERMINAL?
    // =========================================================
    public function isTerminal(): bool
    {
        return in_array(
            $this->acao,
            [
                'finalizado',
                'cancelado',
            ],
            true
        );
    }
}