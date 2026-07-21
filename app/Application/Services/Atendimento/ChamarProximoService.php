<?php

declare(strict_types=1);

namespace App\Application\Services\Atendimento;

use App\Domain\Atendimento\Entities\Atendimento;
use App\Domain\Atendimento\Events\PacienteChamado;
use App\Domain\Atendimento\Repositories\AtendimentoRepositoryInterface;
use App\Infrastructure\Messaging\EventBusInterface;

class ChamarProximoService
{
    public function __construct(
        private AtendimentoRepositoryInterface $repository,
        private EventBusInterface $eventBus
    ) {}

    /**
     * 🚑 CASO DE USO: CHAMADA DE FILA (RNDS / SUS)
     */
    public function executar(): Atendimento
    {
        $atendimento = $this->repository->buscarProximo();

        if (!$atendimento) {
            throw new \DomainException('Nenhum atendimento na fila');
        }

        $atendimento->chamar();

        $this->repository->salvar($atendimento);

        $this->eventBus->dispatch(
            new PacienteChamado(
                $atendimento->id()->value(),
                $atendimento->pacienteId(),
                $atendimento->prioridade(),
                now()->toIso8601String()
            )
        );

        return $atendimento;
    }
}