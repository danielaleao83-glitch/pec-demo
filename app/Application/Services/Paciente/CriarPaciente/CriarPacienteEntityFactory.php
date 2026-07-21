<?php

declare(strict_types=1);

namespace App\Application\Services\Paciente\CriarPaciente;

use App\Domain\Paciente\Entities\Paciente;
use App\Domain\Paciente\ValueObjects\PacienteCpf;
use App\Domain\Paciente\ValueObjects\PacienteCns;
use App\Domain\Paciente\ValueObjects\PacienteId;
use DateTimeImmutable;

final class CriarPacienteEntityFactory
{
    public function make(
        array $payload
    ): Paciente {

        return new Paciente(

            id:
                PacienteId::generate(),

            nome:
                trim(
                    (string) $payload['nome']
                ),

            cpf:
                new PacienteCpf(
                    $payload['cpf']
                ),

            cns:
                new PacienteCns(
                    $payload['cns']
                ),

            dataNascimento:
                new DateTimeImmutable(
                    $payload['data_nascimento']
                ),
        );
    }
}