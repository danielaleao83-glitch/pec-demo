<?php

declare(strict_types=1);

namespace App\Application\Services\Atendimento\Actions\RegistrarFila;

use App\Domain\Atendimento\Entities\Atendimento;
use App\Domain\Auditoria\Contracts\AuditoriaRepositoryInterface;
use App\Domain\Auditoria\Entities\AuditoriaLog;
use App\Domain\Auditoria\Enums\AuditoriaNivelEnum;
use App\Domain\Auditoria\Enums\AuditoriaTipoEnum;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

final class RegistrarFilaAuditoriaAction
{
    public function __construct(
        private readonly AuditoriaRepositoryInterface $repository,
    ) {}

    public function execute(
        Atendimento $atendimento,
        string $correlationId,
        ?string $usuarioId,
        ?string $ip,
        array $metadata
    ): void {

        $log = new AuditoriaLog(
            id: Uuid::uuid7()->toString(),

            tipo:
                AuditoriaTipoEnum::ATENDIMENTO,

            nivel:
                AuditoriaNivelEnum::CRITICO,

            acao:
                'FILA_REGISTRADA',

            entidade:
                Atendimento::class,

            entidadeId:
                $atendimento
                    ->id()
                    ->value(),

            usuarioId:
                $usuarioId,

            ip:
                $ip,

            correlationId:
                $correlationId,

            payload:
                $metadata,

            criadoEm:
                new DateTimeImmutable()
        );

        $this->repository->save($log);
    }
}