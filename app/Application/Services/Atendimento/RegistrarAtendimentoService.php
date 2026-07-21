<?php

declare(strict_types=1);

namespace App\Application\Services\Atendimento;

use App\Domain\Atendimento\DTO\RegistrarAtendimentoDTO;
use App\Domain\Atendimento\Entities\Atendimento;
use App\Domain\Atendimento\Repositories\AtendimentoRepositoryInterface;
use App\Domain\Atendimento\ValueObjects\AtendimentoId;
use App\Domain\Atendimento\ValueObjects\PacienteId;
use App\Domain\Atendimento\ValueObjects\PrioridadeAtendimento;

/**
 * =========================================================
 * 🏥 REGISTRAR ATENDIMENTO SERVICE
 * =========================================================
 */
final class RegistrarAtendimentoService
{
    public function __construct(
        private readonly AtendimentoRepositoryInterface $repository,
    ) {}

    public function execute(
        RegistrarAtendimentoDTO $dto,
        string $correlationId
    ): Atendimento {

        $atendimento =
            new Atendimento(

                id:
                    AtendimentoId::generate(),

                pacienteId:
                    new PacienteId(
                        $dto->pacienteId
                    ),

                prioridade:
                    new PrioridadeAtendimento(
                        $dto->prioridade
                    ),
            );

        $this->repository
            ->save($atendimento);

        return $atendimento;
    }
}