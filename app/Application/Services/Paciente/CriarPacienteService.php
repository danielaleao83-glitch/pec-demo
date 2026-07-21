<?php

declare(strict_types=1);

namespace App\Application\Services\Paciente\CriarPaciente;

use App\Domain\Paciente\Entities\Paciente;

final class CriarPacienteService
{
    public function __construct(
        private readonly CriarPacienteValidator $validator,
        private readonly CriarPacienteDuplicidadeService $duplicidadeService,
        private readonly CriarPacienteEntityFactory $factory,
        private readonly CriarPacienteTransactionService $transactionService,
        private readonly CriarPacienteLogger $logger,
    ) {}

    public function execute(
        array $payload,
        string $correlationId,
        ?string $usuarioId = null,
        ?string $ip = null,
    ): Paciente {

        $this->validator->validate(
            $payload
        );

        $this->duplicidadeService
            ->ensure(
                $payload,
                $correlationId
            );

        $paciente =
            $this->transactionService
                ->execute(
                    payload: $payload,
                    correlationId: $correlationId,
                    usuarioId: $usuarioId,
                    ip: $ip,
                );

        $this->logger->success(
            paciente: $paciente,
            correlationId: $correlationId,
        );

        return $paciente;
    }
}