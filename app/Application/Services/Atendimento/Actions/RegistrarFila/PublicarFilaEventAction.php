<?php

declare(strict_types=1);

namespace App\Application\Services\Atendimento\Actions\RegistrarFila;

use App\Domain\Atendimento\Entities\Atendimento;
use App\Domain\Atendimento\Events\FilaAtualizadaEvent;
use App\Domain\Atendimento\Enums\StatusAtendimento;
use App\Infrastructure\Messaging\EventBus;
use DateTimeImmutable;
use Illuminate\Support\Facades\Log;

final class PublicarFilaEventAction
{
    public function __construct(
        private readonly EventBus $eventBus,
    ) {}

    public function execute(
        Atendimento $atendimento,
        string $correlationId
    ): void {

        $start = microtime(true);

        $evento = $this->buildEvent($atendimento, $correlationId);

        try {

            $this->eventBus->dispatch($evento);

            Log::info('FILA_EVENT_PUBLISHED', [
                'atendimento_id' => $evento->atendimentoId,
                'paciente_id' => $evento->pacienteId,
                'correlation_id' => $correlationId,
                'execution_time_ms' => $this->latency($start),
            ]);

        } catch (\Throwable $e) {

            Log::critical('FILA_EVENT_PUBLISH_FAILED', [
                'atendimento_id' => $atendimento->id()->value(),
                'correlation_id' => $correlationId,
                'error' => $e->getMessage(),
                'execution_time_ms' => $this->latency($start),
            ]);

            throw $e;
        }
    }

    /**
     * 🧠 construção determinística do evento
     */
    private function buildEvent(
        Atendimento $atendimento,
        string $correlationId
    ): FilaAtualizadaEvent {

        return new FilaAtualizadaEvent(
            atendimentoId: $atendimento->id()->value(),
            pacienteId: $atendimento->pacienteId()->value(),

            status: $this->resolveStatus($atendimento),

            prioridade: $atendimento->prioridade()->value(),

            correlationId: $correlationId,

            timestamp: $this->clinicalTimestamp()
        );
    }

    /**
     * 🧠 status derivado do domínio (não hardcoded)
     */
    private function resolveStatus(Atendimento $atendimento): string
    {
        return $atendimento->status()->value
            ?? StatusAtendimento::AGUARDANDO->value;
    }

    /**
     * ⏱ timestamp clínico consistente
     */
    private function clinicalTimestamp(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    private function latency(float $start): float
    {
        return round((microtime(true) - $start) * 1000, 2);
    }
}