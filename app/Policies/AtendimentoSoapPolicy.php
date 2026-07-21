<?php

namespace App\Policies;

use App\Models\AtendimentoSoap;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AtendimentoSoapPolicy
{
    use HandlesAuthorization;

    /*
    |--------------------------------------------------------------------------
    | LISTAR REGISTROS SOAP
    |--------------------------------------------------------------------------
    */

    public function viewAny(User $user)
    {
        return $user->hasRole(['profissional', 'admin']);
    }

    /*
    |--------------------------------------------------------------------------
    | VISUALIZAR SOAP
    |--------------------------------------------------------------------------
    */

    public function view(User $user, AtendimentoSoap $atendimento)
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
    | CRIAR REGISTRO SOAP
    |--------------------------------------------------------------------------
    */

    public function create(User $user)
    {
        return $user->hasRole('profissional');
    }

    /*
    |--------------------------------------------------------------------------
    | ATUALIZAÇÃO BLOQUEADA
    |--------------------------------------------------------------------------
    */

    public function update(User $user, AtendimentoSoap $atendimento)
    {
        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | EXCLUSÃO BLOQUEADA (PRONTUÁRIO MÉDICO)
    |--------------------------------------------------------------------------
    */

    public function delete(User $user, AtendimentoSoap $atendimento)
    {
        return false;
    }
}
