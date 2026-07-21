<?php

declare(strict_types=1);

namespace App\Application\Services\Paciente\CriarPaciente;

final class CriarPacienteHashService
{
    public function generate(
        string $pacienteId,
        string $correlationId,
        string $requestHash,
    ): string {

        return hash(
            'sha512',
            implode('|', [

                $pacienteId,

                $correlationId,

                $requestHash,

                config('app.key'),

                now()->timestamp,
            ])
        );
    }
}