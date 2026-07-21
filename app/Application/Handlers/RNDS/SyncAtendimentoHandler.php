<?php

declare(strict_types=1);

namespace App\Application\Handlers\RNDS;

use App\Domain\Atendimento\Events\AtendimentoFinalizadoEvent;
use App\Infrastructure\Integrations\RNDS\RNDSClient;
use Illuminate\Support\Facades\Log;
use Throwable;

final class SyncAtendimentoHandler
{
    public function __construct(
        private readonly RNDSClient $rndsClient,
    ) {}

    public function handle(AtendimentoFinalizadoEvent $event): void
    {
        $startedAt = microtime(true);

        try {

            $response = $this->rndsClient->syncAtendimento([
                'atendimento_id' => $event->atendimentoId,
                'paciente_id' => $event->pacienteId,
                'status' => $event->status,
                'finalizado_em' => $event->timestamp->format(DATE_ATOM),
                'correlation_id' => $event->correlationId,
            ]);

            Log::channel('security')->info(
                'RNDS_SYNC_ATENDIMENTO_SUCCESS',
                [
                    'status' => 'success',
                    'atendimento_id' => $event->atendimentoId,
                    'paciente_id' => $event->pacienteId,
                    'correlation_id' => $event->correlationId,
                    'execution_time' => round(microtime(true) - $startedAt, 5),
                    'response_status' => $response['status'] ?? null,
                    'synced_at' => now()->toIso8601String(),
                ]
            );

        } catch (Throwable $exception) {

            Log::channel('security')->critical(
                'RNDS_SYNC_ATENDIMENTO_FAILURE',
                [
                    'status' => 'failure',
                    'atendimento_id' => $event->atendimentoId,
                    'paciente_id' => $event->pacienteId,
                    'correlation_id' => $event->correlationId,
                    'error_message' => $exception->getMessage(),
                    'execution_time' => round(microtime(true) - $startedAt, 5),
                    'failed_at' => now()->toIso8601String(),
                ]
            );

            throw $exception;
        }
    }
}