<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Auditoria\Contracts\AuditoriaRepositoryInterface;
use App\Domain\Auditoria\Entities\AuditoriaLog;
use App\Models\Auditoria;

/**
 * =========================================================
 * 🔐 AUDITORIA REPOSITORY
 * =========================================================
 */
final class AuditoriaRepository
    implements AuditoriaRepositoryInterface
{
    public function save(
        AuditoriaLog $log
    ): void {

        Auditoria::query()->create(
            $log->toArray()
        );
    }

    public function find(
        string $id
    ): ?AuditoriaLog {

        $model = Auditoria::query()
            ->where('uuid', $id)
            ->first();

        if (! $model) {
            return null;
        }

        return new AuditoriaLog(
            id:
                \App\Domain\Auditoria\ValueObjects\AuditId::fromString(
                    $model->uuid
                ),

            acao:
                $model->acao,

            modulo:
                $model->modulo,

            payload:
                $model->payload ?? [],

            hashIntegridade:
                \App\Domain\Auditoria\ValueObjects\HashIntegridade::fromString(
                    $model->hash_integridade
                ),

            userId:
                $model->user_id,

            ip:
                $model->ip,

            userAgent:
                $model->user_agent,

            correlationId:
                $model->correlation_id,
        );
    }
}