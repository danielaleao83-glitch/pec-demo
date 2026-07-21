<?php

declare(strict_types=1);

namespace App\Application\Services\Paciente\CriarPaciente;

use App\Domain\Paciente\Entities\Paciente;
use Illuminate\Support\Facades\Log;

final class CriarPacienteLogger
{
    public function success(
        Paciente $paciente,
        string $correlationId,
    ): void {

        Log::channel('security')->info(
            'PACIENTE_CRIADO',
            [

                'paciente_id'
                    => $paciente
                        ->id()
                        ->value(),

                'correlation_id'
                    => $correlationId,

                'timestamp'
                    => now()
                        ->toIso8601String(),
            ]
        );
    }

    public function failure(
        string $message,
        string $correlationId,
    ): void {

        Log::channel('security')->critical(
            'PACIENTE_CREATE_FAILURE',
            [

                'message'
                    => $message,

                'correlation_id'
                    => $correlationId,
            ]
        );
    }
}