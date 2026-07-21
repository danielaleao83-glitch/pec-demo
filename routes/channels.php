<?php

use Illuminate\Support\Facades\Broadcast;

use App\Models\User;
use App\Models\Paciente;
use App\Models\Atendimento;

/*
|--------------------------------------------------------------------------
| 🔐 USER PRIVATE CHANNEL
|--------------------------------------------------------------------------
|
| Canal privado do próprio usuário autenticado
|
*/

Broadcast::channel('App.Models.User.{id}', function (User $user, string $id): bool {

    return (string) $user->id === (string) $id;
});

/*
|--------------------------------------------------------------------------
| 👤 PACIENTE CHANNEL
|--------------------------------------------------------------------------
|
| Controle rígido de acesso a dados clínicos
|
*/

Broadcast::channel('paciente.{pacienteId}', function (
    User $user,
    string $pacienteId
): bool {

    /*
    |--------------------------------------------------------------------------
    | ADMIN TOTAL
    |--------------------------------------------------------------------------
    */
    if ($user->hasRole('admin')) {
        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | VERIFICA ACESSO AO PACIENTE
    |--------------------------------------------------------------------------
    */
    return Paciente::query()
        ->where('id', $pacienteId)
        ->where(function ($query) use ($user) {

            /*
            |--------------------------------------------------------------------------
            | EXEMPLOS:
            | - PROFISSIONAL RESPONSÁVEL
            | - EQUIPE
            | - UBS
            |--------------------------------------------------------------------------
            */

            $query->where('profissional_id', $user->id)

                ->orWhere('unidade_id', $user->unidade_id ?? null)

                ->orWhereHas('equipes', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                });
        })
        ->exists();
});

/*
|--------------------------------------------------------------------------
| 🩺 ATENDIMENTO CHANNEL
|--------------------------------------------------------------------------
|
| Canal privado do atendimento clínico
|
*/

Broadcast::channel('atendimento.{atendimentoId}', function (
    User $user,
    string $atendimentoId
): bool {

    /*
    |--------------------------------------------------------------------------
    | ADMIN
    |--------------------------------------------------------------------------
    */
    if ($user->hasRole('admin')) {
        return true;
    }

    return Atendimento::query()

        ->where('id', $atendimentoId)

        ->where(function ($query) use ($user) {

            $query->where('profissional_id', $user->id)

                ->orWhere('user_id', $user->id)

                ->orWhere('unidade_id', $user->unidade_id ?? null);
        })

        ->exists();
});

/*
|--------------------------------------------------------------------------
| 🔔 NOTIFICAÇÕES PRIVADAS
|--------------------------------------------------------------------------
*/

Broadcast::channel('notificacoes.{userId}', function (
    User $user,
    string $userId
): bool {

    return (string) $user->id === (string) $userId;
});

/*
|--------------------------------------------------------------------------
| 🏥 DASHBOARD OPERACIONAL
|--------------------------------------------------------------------------
|
| Canal operacional hospitalar
|
*/

Broadcast::channel('dashboard.operacional', function (User $user): bool {

    return $user->hasAnyRole([
        'admin',
        'gestor',
        'coordenador',
        'recepcao',
    ]);
});

/*
|--------------------------------------------------------------------------
| 🚑 FILA / TRIAGEM
|--------------------------------------------------------------------------
*/

Broadcast::channel('fila.atendimento', function (User $user): bool {

    return $user->hasAnyRole([
        'admin',
        'triagem',
        'enfermeiro',
        'medico',
        'recepcao',
    ]);
});

/*
|--------------------------------------------------------------------------
| 📡 SUS INTEGRAÇÃO
|--------------------------------------------------------------------------
*/

Broadcast::channel('sus.integracao', function (User $user): bool {

    return $user->hasAnyRole([
        'admin',
        'gestor',
        'integracao_sus',
    ]);
});

/*
|--------------------------------------------------------------------------
| 🧠 AUDITORIA / SEGURANÇA
|--------------------------------------------------------------------------
*/

Broadcast::channel('security.audit', function (User $user): bool {

    return $user->hasAnyRole([
        'admin',
        'compliance',
        'auditor',
        'security',
    ]);
});