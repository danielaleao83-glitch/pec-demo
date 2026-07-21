<?php

namespace App\Actions\Auth;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UpdatePasswordAction
{
    /**
     * Atualiza a senha do usuário com segurança básica de produção.
     */
    public function execute($user, string $newPassword): void
    {
        $user->password = Hash::make($newPassword);

        // invalida sessões persistentes ("remember me")
        $user->setRememberToken(Str::random(60));

        $user->save();
    }
}