<?php

declare(strict_types=1);

namespace App\Application\Services\Auth\Logout;

use App\Domain\Auditoria\Contracts\AuditoriaRepositoryInterface;
use App\Domain\Auditoria\Entities\AuditoriaLog;
use App\Domain\Auditoria\Enums\AuditoriaNivelEnum;
use App\Domain\Auditoria\Enums\AuditoriaTipoEnum;
use App\Models\User;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

/**
 * =========================================================
 * 🔐 LOGOUT AUDIT SERVICE
 * 🏥 e-SUS APS / DATASUS / RNDS
 * =========================================================
 *
 * ✔ Auditoria forense
 * ✔ Produção federal
 * ✔ LGPD
 * ✔ Rastreabilidade nacional
 * ✔ Integridade hospitalar
 *
 * =========================================================
 */
final class LogoutAuditService
{
    public function __construct(
        private readonly AuditoriaRepositoryInterface $repository,
    ) {}

    /**
     * =========================================================
     * 🧾 REGISTRAR AUDITORIA
     * =========================================================
     */
    public function register(
        User $user,
        string $correlationId,
        ?string $ip,
        ?string $userAgent,
        string $hashIntegridade,
    ): void {

        $log = new AuditoriaLog(

            id:
                Uuid::uuid7()
                    ->toString(),

            tipo:
                AuditoriaTipoEnum
                    ::AUTENTICACAO,

            nivel:
                AuditoriaNivelEnum
                    ::CRITICO,

            acao:
                'LOGOUT_REALIZADO',

            entidade:
                User::class,

            entidadeId:
                (string) $user->id,

            usuarioId:
                (string) $user->id,

            ip:
                $ip,

            correlationId:
                $correlationId,

            payload: [

                'email'
                    => $user->email,

                'user_agent'
                    => substr(
                        (string) $userAgent,
                        0,
                        500
                    ),

                'hash_integridade'
                    => $hashIntegridade,
            ],

            criadoEm:
                new DateTimeImmutable()
        );

        $this->repository->save($log);
    }
}