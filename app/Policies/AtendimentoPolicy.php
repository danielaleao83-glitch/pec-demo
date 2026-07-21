<?php

namespace App\Policies;

use App\Models\Atendimento;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AtendimentoPolicy
{
    use HandlesAuthorization;

    /*
    |--------------------------------------------------------------------------
    | LISTAR ATENDIMENTOS
    |--------------------------------------------------------------------------
    */

    public function viewAny(User $user)
    {
        return $user->hasRole(['medico', 'enfermeiro', 'admin']);
    }

    /*
    |--------------------------------------------------------------------------
    | VISUALIZAR ATENDIMENTO
    |--------------------------------------------------------------------------
    */

    public function view(User $user, Atendimento $atendimento)
    {

        // profissional responsável
        if ($user->id === $atendimento->profissional_id) {
            return true;
        }

        // administrador
        if ($user->hasRole('admin')) {
            return true;
        }

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | CRIAR ATENDIMENTO
    |--------------------------------------------------------------------------
    */

    public function create(User $user)
    {
        return $user->hasRole(['medico', 'enfermeiro']);
    }

    /*
    |--------------------------------------------------------------------------
    | ATUALIZAR (PROTEÇÃO DO HISTÓRICO)
    |--------------------------------------------------------------------------
    */

    public function update(User $user, Atendimento $atendimento)
    {
        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | EXCLUSÃO BLOQUEADA
    |--------------------------------------------------------------------------
    */

    public function delete(User $user, Atendimento $atendimento)
    {
        return false;
    }
}
