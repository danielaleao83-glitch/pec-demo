<?php

declare(strict_types=1);

namespace App\Domain\Atendimento\DTO;

use DateTimeImmutable;
use Ramsey\Uuid\Uuid;
use InvalidArgumentException;

/**
 * =========================================================
 * 🏥 REGISTRAR ATENDIMENTO DTO
 * =========================================================
 *
 * ✔ Imutável
 * ✔ UUID-first
 * ✔ Auditável
 * ✔ Replay-safe
 * ✔ RNDS Ready
 * ✔ Event-Sourcing Ready
 * ✔ Determinístico
 *
 * =========================================================
 */
final readonly class RegistrarAtendimentoDTO
{
    public function __construct(
        public string $pacienteId,
        public int $prioridade,
        public string $unidadeId,
        public string $profissionalId,
        public ?string $observacao,
        public string $correlationId,
        public string $requestId,
        public DateTimeImmutable $timestamp,
    ) {

        $this->validate();
    }

    // =========================================================
    // 🔐 FACTORY
    // =========================================================
    public static function create(
        string $pacienteId,
        int $prioridade,
        string $unidadeId,
        string $profissionalId,
        ?string $observacao = null,
        ?string $correlationId = null
    ): self {

        return new self(
            pacienteId: $pacienteId,
            prioridade: $prioridade,
            unidadeId: $unidadeId,
            profissionalId: $profissionalId,
            observacao: $observacao,
            correlationId: $correlationId
                ?? Uuid::uuid7()->toString(),
            requestId: Uuid::uuid7()->toString(),
            timestamp: new DateTimeImmutable()
        );
    }

    // =========================================================
    // 🔐 VALIDAÇÃO
    // =========================================================
    private function validate(): void
    {
        foreach ([
            $this->pacienteId,
            $this->unidadeId,
            $this->profissionalId,
            $this->correlationId,
            $this->requestId,
        ] as $uuid) {

            if (!Uuid::isValid($uuid)) {
                throw new InvalidArgumentException(
                    'UUID inválido informado'
                );
            }
        }

        if ($this->prioridade < 0) {
            throw new InvalidArgumentException(
                'Prioridade inválida'
            );
        }
    }

    // =========================================================
    // 🧾 SERIALIZAÇÃO DETERMINÍSTICA
    // =========================================================
    public function toArray(): array
    {
        return [

            'paciente_id' => $this->pacienteId,

            'prioridade' => $this->prioridade,

            'unidade_id' => $this->unidadeId,

            'profissional_id' => $this->profissionalId,

            'observacao' => $this->observacao,

            'correlation_id' => $this->correlationId,

            'request_id' => $this->requestId,

            'timestamp' => $this->timestamp
                ->format(DATE_ATOM),
        ];
    }

    // =========================================================
    // 🔐 HASH AUDITÁVEL
    // =========================================================
    public function hash(): string
    {
        return hash(
            'sha256',
            json_encode(
                $this->toArray(),
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
            )
        );
    }
}