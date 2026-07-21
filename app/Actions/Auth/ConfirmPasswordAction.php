<?php

namespace App\Actions\Auth;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ConfirmPasswordAction
{
    /**
     * Confirma se a senha informada corresponde ao hash armazenado.
     *
     * Uso: validação de identidade já autenticada em fluxo protegido.
     */
    public function execute(string $password, string $hashedPassword): bool
    {
        if (! Hash::check($password, $hashedPassword)) {
            throw ValidationException::withMessages([
                'password' => 'Senha inválida.'
            ]);
        }

        return true;
    }
}