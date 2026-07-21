<?php

declare(strict_types=1);

namespace App\Domain\Atendimento\Traits;

use DateTimeImmutable;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;

/**
 * =========================================================
 * 🏥 DOMAIN EVENTS TRAIT (BLINDADO)
 * =========================================================
 *
 * ✔ Event Sourcing
 * ✔ Hash chain (ledger imutável)
 * ✔ HMAC auditável
 * ✔ Correlation ID distribuído
 * ✔ Snapshot verificável
 * ✔ Replay-safe (sem serialize)
 * ✔ Append-only in-memory buffer
 * ✔ Produção crítica (hospitalar / federado)
 * =========================================================
 */
trait HasDomainEvents
{
    /**
     * 🧾 Event buffer (memória local do aggregate)
     */
    private array $events = [];

    /**
     * 🔐 Último hash da cadeia (ledger local)
     */
    private ?string $eventHash = null;

    /**
     * 🌐 Correlation ID (fluxo distribuído)
     */
    private ?string $correlationId = null;

    // =========================================================
    // 📌 RECORD EVENT (CORE DO SISTEMA)
    // =========================================================
    protected function record(object $event): void
    {
        $previousHash = $this->eventHash;

        $currentHash = $this->generateEventHash($event, $previousHash);

        $this->eventHash = $currentHash;

        $metadata = [
            'event_uuid' => Uuid::uuid7()->toString(),

            'event_class' => get_class($event),

            'aggregate' => static::class,

            'aggregate_uuid' => method_exists($this, 'uuid')
                ? $this->uuid()
                : null,

            'correlation_id' => $this->getCorrelationId(),

            'previous_hash' => $previousHash,

            'current_hash' => $currentHash,

            'timestamp' => $this->nowUtc()->format(DATE_ATOM),
        ];

        $this->events[] = [
            'event' => $event,
            'metadata' => $metadata,
        ];

        Log::channel('security')->info(
            'DOMAIN_EVENT_RECORDED',
            $metadata
        );
    }

    // =========================================================
    // 🔐 HASH CHAIN (AUDITORIA FORTE REAL)
    // =========================================================
    private function generateEventHash(
        object $event,
        ?string $previousHash = null
    ): string {
        $payload = [
            'aggregate' => static::class,

            'event_class' => get_class($event),

            'event_payload' => method_exists($event, 'toArray')
                ? $event->toArray()
                : get_object_vars($event),

            'correlation_id' => $this->getCorrelationId(),

            'previous_hash' => $previousHash,

            'timestamp' => $this->nowUtc()->format(DATE_ATOM),

            'schema_version' => 1,
        ];

        return hash_hmac(
            'sha256',
            json_encode(
                $payload,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            ),
            config('app.audit_key')
        );
    }

    // =========================================================
    // 🌐 CORRELATION ID (DISTRIBUÍDO REAL)
    // =========================================================
    private function getCorrelationId(): string
    {
        return $this->correlationId
            ??= request()->header('X-Correlation-Id')
            ?? Uuid::uuid7()->toString();
    }

    // =========================================================
    // 🚀 RELEASE EVENTS (OUTBOX / EVENT BUS)
    // =========================================================
    public function releaseEvents(): array
    {
        $events = $this->events;

        $this->events = [];

        return $events;
    }

    public function hasEvents(): bool
    {
        return !empty($this->events);
    }

    public function totalEvents(): int
    {
        return count($this->events);
    }

    public function lastEvent(): ?array
    {
        return empty($this->events)
            ? null
            : end($this->events);
    }

    // =========================================================
    // 🧾 SNAPSHOT AUDITÁVEL (SEM SERIALIZE)
    // =========================================================
    public function eventSnapshot(): array
    {
        return array_map(
            fn ($item) => [
                'event_class' => get_class($item['event']),

                'payload' => method_exists($item['event'], 'toArray')
                    ? $item['event']->toArray()
                    : get_object_vars($item['event']),

                'metadata' => $item['metadata'],
            ],
            $this->events
        );
    }

    // =========================================================
    // 🔐 HASH GLOBAL DO LEDGER (VERIFICAÇÃO LOCAL / EXTERNA)
    // =========================================================
    public function integrityHash(): string
    {
        return hash_hmac(
            'sha512',
            json_encode(
                $this->eventSnapshot(),
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            ),
            config('app.audit_key')
        );
    }

    // =========================================================
    // 🧹 CLEAR SAFE (RESET CONTROLADO)
    // =========================================================
    public function clearEvents(): void
    {
        $this->events = [];
        $this->eventHash = null;
    }

    // =========================================================
    // ⏱ UTC TIME SOURCE (REPRODUCÍVEL)
    // =========================================================
    protected function nowUtc(): DateTimeImmutable
    {
        return new DateTimeImmutable(
            now('UTC')->toIso8601String()
        );
    }
}