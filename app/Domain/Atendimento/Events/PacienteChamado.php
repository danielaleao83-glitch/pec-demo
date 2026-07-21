<?php

declare(strict_types=1);

namespace App\Domain\Atendimento\Events;

use App\Domain\Shared\Events\DomainEvent;

/**
 * =========================================================
 * 🏥 PACIENTE CHAMADO
 * =========================================================
 *
 * ✔ UUID v7 (herdado do DomainEvent)
 * ✔ Correlation ID
 * ✔ Causation ID
 * ✔ Event Versioning
 * ✔ Replay Safe
 * ✔ Event Store Ready
 * ✔ Outbox Ready
 * ✔ Auditoria Forense
 * ✔ Serialização Determinística
 * ✔ Compatível com Workflow de Atendimento
 *
 * =========================================================
 */
final class PacienteChamado extends DomainEvent
{
    public const EVENT_NAME = 'atendimento.paciente_chamado';

    public const EVENT_VERSION = 1;

    public function __construct(
        string $aggregateId,
        string $correlationId,

        public readonly string $pacienteId,
        public readonly int $prioridade,

        public readonly string $guicheId,
        public readonly string $profissionalId,

        public readonly ?string $causationId = null,
        public readonly ?string $unidadeId = null,
        public readonly ?string $setorId = null,
    ) {
        parent::__construct(
            aggregateId: $aggregateId,
            correlationId: $correlationId
        );
    }

    public function eventName(): string
    {
        return self::EVENT_NAME;
    }

    public function eventVersion(): int
    {
        return self::EVENT_VERSION;
    }

    public function payload(): array
    {
        return [

            'paciente_id' => $this->pacienteId,

            'prioridade' => $this->prioridade,

            'guiche_id' => $this->guicheId,

            'profissional_id' => $this->profissionalId,

            'unidade_id' => $this->unidadeId,

            'setor_id' => $this->setorId,

            'causation_id' => $this->causationId,
        ];
    }

    public function fingerprint(): string
    {
        return hash(
            'sha256',
            json_encode(
                $this->payload(),
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
            )
        );
    }
}