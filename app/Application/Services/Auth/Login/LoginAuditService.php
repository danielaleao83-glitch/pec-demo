<?php

declare(strict_types=1);

namespace App\Application\Services\Auth\Login;

use App\Domain\Auditoria\Contracts\AuditoriaRepositoryInterface;
use App\Domain\Auditoria\Entities\AuditoriaLog;
use App\Domain\Auditoria\Enums\AuditoriaNivelEnum;
use App\Domain\Auditoria\Enums\AuditoriaTipoEnum;
use App\Models\User;
use DateTimeImmutable;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

final class LoginAuditService
{
    public function __construct(
        private readonly AuditoriaRepositoryInterface $repository,
    ) {}

    public function log(
        User $user,
        Request $request,
        string $correlationId,
        string $hashIntegridade
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
                'LOGIN_REALIZADO',

            entidade:
                User::class,

            entidadeId:
                (string) $user->id,

            usuarioId:
                (string) $user->id,

            ip:
                $request->ip(),

            correlationId:
                $correlationId,

            payload: [

                'email'
                    => $user->email,

                'user_agent'
                    => substr(
                        (string) $request->userAgent(),
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