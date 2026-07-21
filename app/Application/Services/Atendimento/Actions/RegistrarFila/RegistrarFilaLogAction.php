<?php

declare(strict_types=1);

namespace App\Application\Services\Atendimento\Actions\RegistrarFila;

use App\Domain\Atendimento\Entities\Atendimento;
use Illuminate\Support\Facades\Log;
use Throwable;

final class RegistrarFilaLogAction
{
    public function execute(
        Atendimento $atendimento,
        string $correlationId,
        float $executionTime
    ): void {

        Log::channel('security')->info(
            'FILA_REGISTRADA',
            [

                'atendimento_id'
                    => $atendimento
                        ->id()
                        ->value(),

                'paciente_id'
                    => $atendimento
                        ->pacienteId()
                        ->value(),

                'status'
                    => $atendimento
                        ->status()
                        ->value,

                'correlation_id'
                    => $correlationId,

                'execution_time'
                    => $executionTime,
            ]
        );
    }

    public function critical(
        Throwable $exception,
        string $correlationId,
        string $pacienteId
    ): void {

        Log::channel('security')->critical(
            'FILA_REGISTRO_FAILURE',
            [

                'message'
                    => $exception
                        ->getMessage(),

                'correlation_id'
                    => $correlationId,

                'paciente_id'
                    => $pacienteId,
            ]
        );
    }
}