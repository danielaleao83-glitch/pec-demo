<?php

namespace App\Actions\Auth;

use Illuminate\Support\Facades\Auth;

class LogoutUserAction
{
    /**
     * Encerra a sessão do usuário de forma segura.
     *
     * Observação:
     * Logout é evento de segurança, mas aqui mantemos implementação simples
     * para estabilidade de produção.
     */
    public function execute(): void
    {
        Auth::logout();

        $this->invalidateSession();
    }

    private function invalidateSession(): void
    {
        $request = request();

        if ($request && $request->session()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
    }
}