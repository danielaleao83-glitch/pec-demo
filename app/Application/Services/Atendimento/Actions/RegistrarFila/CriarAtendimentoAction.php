<?php

declare(strict_types=1);

namespace App\Application\Services\Atendimento\Actions\RegistrarFila;

use App\Domain\Atendimento\Contracts\AtendimentoRepositoryInterface;
use App\Domain\Atendimento\Entities\Atendimento;
use App\Domain\Atendimento\Services\FilaIntegrityService;
use App\Domain\Atendimento\ValueObjects\AtendimentoId;
use App\Domain\Atendimento\ValueObjects\PacienteId;
use App\Domain\Atendimento\ValueObjects\PrioridadeAtendimento;

final class CriarAtendimentoAction
{
    public function __construct(
        private readonly AtendimentoRepositoryInterface $repository,
        private readonly FilaIntegrityService $integrityService,
    ) {}

    public function execute(
        string $pacienteId,
        int $prioridade,
        string $correlationId,
        string $requestHash
    ): Atendimento {

        $atendimento =
            new Atendimento(
                id: AtendimentoId::generate(),
                pacienteId: new PacienteId(
                    $pacienteId
                ),
                prioridade:
                    new PrioridadeAtendimento(
                        $prioridade
                    ),
            );

        $this->integrityService->generate(
            atendimento: $atendimento,
            correlationId: $correlationId,
            payload: [
                'request_hash'
                    => $requestHash,
            ]
        );

        $this->repository->save(
            $atendimento
        );

        return $atendimento;
    }
}<?php

declare(strict_types=1);

namespace App\Application\Services\Atendimento\Actions\RegistrarFila;

use App\Domain\Atendimento\Contracts\AtendimentoRepositoryInterface;
use App\Domain\Atendimento\Entities\Atendimento;
use App\Domain\Atendimento\Services\FilaIntegrityService;
use App\Domain\Atendimento\ValueObjects\AtendimentoId;
use App\Domain\Atendimento\ValueObjects\PacienteId;
use App\Domain\Atendimento\ValueObjects\PrioridadeAtendimento;
use Illuminate\Support\Facades\Log;

final class CriarAtendimentoAction
{
    public function __construct(
        private readonly AtendimentoRepositoryInterface $repository,
        private readonly FilaIntegrityService $integrityService,
    ) {}

    public function execute(
        string $pacienteId,
        int $prioridade,
        string $correlationId,
        string $requestHash
    ): Atendimento {

        $start = microtime(true);

        $atendimento = new Atendimento(
            id: AtendimentoId::generate(),
            pacienteId: new PacienteId($pacienteId),
            prioridade: new PrioridadeAtendimento($prioridade),
        );

        try {

            /**
             * 🔐 INTEGRIDADE PRIMEIRO (pré-persistência)
             */
            $this->integrityService->generate(
                atendimento: $atendimento,
                correlationId: $correlationId,
                payload: [
                    'request_hash' => $this->normalizeHash($requestHash),
                ]
            );

            /**
             * 💾 PERSISTÊNCIA ATÔMICA
             */
            $this->repository->save($atendimento);

            Log::info('ATENDIMENTO_CRIADO', [
                'atendimento_id' => (string) $atendimento->id,
                'paciente_id' => $pacienteId,
                'correlation_id' => $correlationId,
                'prioridade' => $prioridade,
                'execution_time_ms' => $this->latency($start),
                'status' => 'success',
            ]);

            return $atendimento;

        } catch (\Throwable $e) {

            Log::critical('ATENDIMENTO_CRIACAO_FALHA', [
                'paciente_id' => $pacienteId,
                'correlation_id' => $correlationId,
                'error_type' => $e::class,
                'message' => $e->getMessage(),
                'execution_time_ms' => $this->latency($start),
                'status' => 'failure',
            ]);

            throw $e;
        }
    }

    /**
     * 🔐 normalização de hash para evitar inconsistência silenciosa
     */
    private function normalizeHash(string $hash): string
    {
        return trim(strtolower($hash));
    }

    /**
     * ⏱ métrica operacional real
     */
    private function latency(float $start): float
    {
        return round((microtime(true) - $start) * 1000, 2);
    }
}