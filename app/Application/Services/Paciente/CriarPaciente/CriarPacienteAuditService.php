<?php

declare(strict_types=1);

namespace App\Application\Services\Paciente\CriarPaciente;

use App\Domain\Auditoria\Contracts\AuditoriaRepositoryInterface;
use App\Domain\Auditoria\Entities\AuditoriaLog;
use App\Domain\Auditoria\Enums\AuditoriaNivelEnum;
use App\Domain\Auditoria\Enums\AuditoriaTipoEnum;
use App\Domain\Paciente\Entities\Paciente;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

final class CriarPacienteAuditService
{
    public function __construct(
        private readonly AuditoriaRepositoryInterface $repository,
    ) {}

    public function register(
        Paciente $paciente,
        string $correlationId,
        string $hashIntegridade,
        ?string $usuarioId,
        ?string $ip,
    ): void {

        $log = new AuditoriaLog(

            id:
                Uuid::uuid7()
                    ->toString(),

            tipo:
                AuditoriaTipoEnum
                    ::PACIENTE,

            nivel:
                AuditoriaNivelEnum
                    ::CRITICO,

            acao:
                'PACIENTE_CRIADO',

            entidade:
                Paciente::class,

            entidadeId:
                $paciente
                    ->id()
                    ->value(),

            usuarioId:
                $usuarioId,

            ip:
                $ip,

            correlationId:
                $correlationId,

            payload: [

                'hash_integridade'
                    => $hashIntegridade,
            ],

            criadoEm:
                new DateTimeImmutable(),
        );

        $this->repository->save(
            $log
        );
    }
}