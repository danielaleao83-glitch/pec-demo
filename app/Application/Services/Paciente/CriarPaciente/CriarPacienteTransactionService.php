<?php

declare(strict_types=1);

namespace App\Application\Services\Paciente\CriarPaciente;

use App\Domain\Paciente\Contracts\PacienteRepositoryInterface;
use App\Domain\Paciente\Entities\Paciente;
use Illuminate\Support\Facades\DB;

final class CriarPacienteTransactionService
{
    public function __construct(
        private readonly PacienteRepositoryInterface $repository,
        private readonly CriarPacienteEntityFactory $factory,
        private readonly CriarPacienteHashService $hashService,
        private readonly CriarPacienteAuditService $auditService,
    ) {}

    public function execute(
        array $payload,
        string $correlationId,
        ?string $usuarioId,
        ?string $ip,
    ): Paciente {

        return DB::transaction(
            function () use (
                $payload,
                $correlationId,
                $usuarioId,
                $ip
            ) {

                $paciente =
                    $this->factory->make(
                        $payload
                    );

                $this->repository->save(
                    $paciente
                );

                $requestHash = hash(
                    'sha512',
                    json_encode(
                        $payload,
                        JSON_UNESCAPED_UNICODE
                    )
                );

                $hashIntegridade =
                    $this->hashService
                        ->generate(
                            pacienteId:
                                $paciente
                                    ->id()
                                    ->value(),

                            correlationId:
                                $correlationId,

                            requestHash:
                                $requestHash,
                        );

                $this->auditService
                    ->register(
                        paciente:
                            $paciente,

                        correlationId:
                            $correlationId,

                        hashIntegridade:
                            $hashIntegridade,

                        usuarioId:
                            $usuarioId,

                        ip:
                            $ip,
                    );

                return $paciente;
            }
        );
    }
}