<?php

declare(strict_types=1);

namespace App\Domain\Auditoria\Entities;

use App\Domain\Auditoria\ValueObjects\AuditId;
use App\Domain\Auditoria\ValueObjects\HashIntegridade;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

/**
 * =========================================================
 * 🔐 AUDITORIA LEDGER LOG (IMUTÁVEL / FEDERADO)
 * =========================================================
 *
 * ✔ Append-only semantic record
 * ✔ Hash chain support
 * ✔ Versionamento de schema
 * ✔ Correlation trace
 * ✔ Integridade verificável
 * ✔ Base para replay / RNDS / SUS
 *
 * =========================================================
 */
final class AuditoriaLog
{
    private readonly string $uuid;
    private readonly DateTimeImmutable $executadoEm;

    public function __construct(
        private readonly AuditId $id,
        private readonly string $acao,
        private readonly string $modulo,
        private readonly array $payload,
        private readonly HashIntegridade $hashIntegridade,

        private readonly ?string $previousHash = null,
        private readonly int $schemaVersion = 1,

        private readonly ?string $userId = null,
        private readonly ?string $ip = null,
        private readonly ?string $userAgent = null,
        private readonly ?string $correlationId = null,
    ) {
        $this->uuid = Uuid::uuid7()->toString();
        $this->executadoEm = new DateTimeImmutable();
    }

    // =========================================================
    // 🧾 SERIALIZAÇÃO AUDITÁVEL (LEDGER SAFE)
    // =========================================================
    public function toArray(): array
    {
        return [
            'id' => $this->id->value(),
            'uuid' => $this->uuid,

            'acao' => $this->acao,
            'modulo' => $this->modulo,

            'payload' => $this->normalizedPayload(),

            'previous_hash' => $this->previousHash,

            'hash_integridade' => $this->hashIntegridade->value(),

            'schema_version' => $this->schemaVersion,

            'correlation_id' => $this->correlationId,

            'user_id' => $this->userId,
            'ip' => $this->ip,
            'user_agent' => $this->userAgent,

            'executado_em' => $this->executadoEm->format(DATE_ATOM),
        ];
    }

    // =========================================================
    // 🔐 NORMALIZAÇÃO (EVITA BYPASS DE AUDITORIA)
    // =========================================================
    private function normalizedPayload(): array
    {
        ksort($this->payload);

        return $this->payload;
    }

    // =========================================================
    // 🧠 IDENTIDADE DO REGISTRO
    // =========================================================
    public function uuid(): string
    {
        return $this->uuid;
    }

    public function correlationId(): ?string
    {
        return $this->correlationId;
    }

    public function previousHash(): ?string
    {
        return $this->previousHash;
    }

    public function hash(): string
    {
        return $this->hashIntegridade->value();
    }

    public function executedAt(): DateTimeImmutable
    {
        return $this->executadoEm;
    }

    // =========================================================
    // 🧠 REGRAS DE INTEGRIDADE SEMÂNTICA
    // =========================================================
    public function isLinked(): bool
    {
        return $this->previousHash !== null;
    }
}