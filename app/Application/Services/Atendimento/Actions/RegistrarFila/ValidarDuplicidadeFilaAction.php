<?php

declare(strict_types=1);

namespace App\Application\Services\Atendimento\Actions\RegistrarFila;

use App\Domain\Atendimento\Exceptions\AtendimentoDomainException;
use Illuminate\Support\Facades\Cache;

final class ValidarDuplicidadeFilaAction
{
    private const TTL = 120;

    public function execute(
        string $hash
    ): void {

        $cacheKey =
            'fila_duplicate_' . sha1($hash);

        if (Cache::has($cacheKey)) {

            throw new AtendimentoDomainException(
                'Registro duplicado detectado.'
            );
        }

        Cache::put(
            $cacheKey,
            true,
            self::TTL
        );
    }
}