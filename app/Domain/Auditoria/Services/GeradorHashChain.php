<?php

declare(strict_types=1);

namespace App\Domain\Auditoria\Services;

use App\Domain\Auditoria\Entities\AuditoriaLog;
use App\Domain\Auditoria\ValueObjects\HashIntegridade;

/**
 * =========================================================
 * 🔐 GERADOR HASH CHAIN
 * =========================================================
 *
 * ✔ Cadeia forense
 * ✔ Blockchain style
 * ✔ Integridade federal
 *
 * =========================================================
 */
final class GeradorHashChain
{
    public function generateHash(
        string $aggregateId,
        string $action,
        array $payload = [],
        ?string $previousHash = null
    ): string {

        return hash(
            'sha512',
            implode('|', [

                $aggregateId,

                $action,

                json_encode(
                    $payload,
                    JSON_UNESCAPED_UNICODE
                ),

                $previousHash,

                now()->timestamp,

                config('app.key'),
            ])
        );
    }

    public function chain(
        AuditoriaLog $log,
        ?string $previousHash = null
    ): HashIntegridade {

        return HashIntegridade::fromString(
            $this->generateHash(
                aggregateId:
                    $log->toArray()['uuid'],

                action:
                    $log->toArray()['acao'],

                payload:
                    $log->toArray(),

                previousHash:
                    $previousHash
            )
        );
    }
}