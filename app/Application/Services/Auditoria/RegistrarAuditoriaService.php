<?php

declare(strict_types=1);

namespace App\Application\Services\Auditoria;

use App\Domain\Auditoria\Contracts\AuditoriaRepositoryInterface;
use App\Domain\Auditoria\Entities\AuditoriaLog;
use App\Domain\Auditoria\Services\GeradorHashChain;
use App\Domain\Auditoria\ValueObjects\AuditId;
use App\Domain\Auditoria\ValueObjects\HashIntegridade;

/**
 * =========================================================
 * 🔐 REGISTRAR AUDITORIA SERVICE
 * =========================================================
 *
 * ✔ Hash chain
 * ✔ Produção federal
 * ✔ Auditoria RNDS
 *
 * =========================================================
 */
final class RegistrarAuditoriaService
{
    public function __construct(
        private readonly AuditoriaRepositoryInterface $repository,
        private readonly GeradorHashChain $hashChain,
    ) {}

    public function execute(
        string $acao,
        string $modulo,
        array $payload = [],
        ?string $userId = null,
        ?string $ip = null,
        ?string $userAgent = null,
        ?string $correlationId = null,
        ?string $previousHash = null,
    ): AuditoriaLog {

        $hash =
            $this->hashChain->generateHash(
                aggregateId:
                    $correlationId
                    ?? 'SYSTEM',

                action:
                    $acao,

                payload:
                    $payload,

                previousHash:
                    $previousHash
            );

        $log = new AuditoriaLog(

            id:
                AuditId::generate(),

            acao:
                $acao,

            modulo:
                $modulo,

            payload:
                $payload,

            hashIntegridade:
                HashIntegridade::fromString(
                    $hash
                ),

            userId:
                $userId,

            ip:
                $ip,

            userAgent:
                $userAgent,

            correlationId:
                $correlationId,
        );

        $this->repository->save(
            $log
        );

        return $log;
    }
}