<?php

declare(strict_types=1);

namespace App\Application\Services\Atendimento;

use App\Application\Services\Atendimento\Actions\RegistrarFila\CriarAtendimentoAction;
use App\Application\Services\Atendimento\Actions\RegistrarFila\GerarFilaHashAction;
use App\Application\Services\Atendimento\Actions\RegistrarFila\PublicarFilaEventAction;
use App\Application\Services\Atendimento\Actions\RegistrarFila\RegistrarFilaAuditoriaAction;
use App\Application\Services\Atendimento\Actions\RegistrarFila\RegistrarFilaLogAction;
use App\Application\Services\Atendimento\Actions\RegistrarFila\ValidarDuplicidadeFilaAction;
use App\Domain\Atendimento\Entities\Atendimento;
use Illuminate\Support\Facades\DB;
use Throwable;

final class RegistrarFilaService
{
    public function __construct(
        private readonly ValidarDuplicidadeFilaAction $duplicidade,
        private readonly CriarAtendimentoAction $criarAtendimento,
        private readonly GerarFilaHashAction $hashAction,
        private readonly PublicarFilaEventAction $eventAction,
        private readonly RegistrarFilaAuditoriaAction $auditoriaAction,
        private readonly RegistrarFilaLogAction $logAction,
    ) {}

    public function execute(
        string $pacienteId,
        int $prioridade,
        string $correlationId,
        ?string $usuarioId = null,
        ?string $ip = null,
        array $metadata = [],
    ): Atendimento {

        $startedAt = microtime(true);

        $requestHash = $this->hashAction->execute(
            pacienteId: $pacienteId,
            prioridade: $prioridade,
            correlationId: $correlationId
        );

        $this->duplicidade->execute($requestHash);

        try {

            return DB::transaction(
                function () use (
                    $pacienteId,
                    $prioridade,
                    $correlationId,
                    $usuarioId,
                    $ip,
                    $metadata,
                    $requestHash,
                    $startedAt
                ) {

                    $atendimento =
                        $this->criarAtendimento
                            ->execute(
                                pacienteId: $pacienteId,
                                prioridade: $prioridade,
                                correlationId: $correlationId,
                                requestHash: $requestHash
                            );

                    $this->eventAction->execute(
                        atendimento: $atendimento,
                        correlationId: $correlationId
                    );

                    $this->auditoriaAction->execute(
                        atendimento: $atendimento,
                        correlationId: $correlationId,
                        usuarioId: $usuarioId,
                        ip: $ip,
                        metadata: $metadata
                    );

                    $this->logAction->execute(
                        atendimento: $atendimento,
                        correlationId: $correlationId,
                        executionTime: round(
                            microtime(true)
                            - $startedAt,
                            5
                        )
                    );

                    return $atendimento;
                }
            );

        } catch (Throwable $exception) {

            $this->logAction->critical(
                exception: $exception,
                correlationId: $correlationId,
                pacienteId: $pacienteId
            );

            throw $exception;
        }
    }
}