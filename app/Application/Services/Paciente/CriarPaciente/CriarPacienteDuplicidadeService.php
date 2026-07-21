<?php

declare(strict_types=1);

namespace App\Application\Services\Paciente\CriarPaciente;

use App\Domain\Paciente\Contracts\PacienteRepositoryInterface;
use App\Domain\Paciente\Exceptions\PacienteException;
use Illuminate\Support\Facades\Cache;

final class CriarPacienteDuplicidadeService
{
    private const TTL = 120;

    public function __construct(
        private readonly PacienteRepositoryInterface $repository,
    ) {}

    public function ensure(
        array $payload,
        string $correlationId,
    ): void {

        $hash = hash(
            'sha512',
            implode('|', [

                $payload['cpf'],

                $payload['cns'],

                $correlationId,
            ])
        );

        $cacheKey =
            'paciente_duplicate_'
            . sha1($hash);

        if (Cache::has($cacheKey)) {

            throw new PacienteException(
                'Duplicidade detectada.'
            );
        }

        Cache::put(
            $cacheKey,
            true,
            self::TTL
        );

        if (
            $this->repository
                ->existsByCpf(
                    $payload['cpf']
                )
        ) {

            throw new PacienteException(
                'CPF já cadastrado.'
            );
        }

        if (
            $this->repository
                ->existsByCns(
                    $payload['cns']
                )
        ) {

            throw new PacienteException(
                'CNS já cadastrado.'
            );
        }
    }
}