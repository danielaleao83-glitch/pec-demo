<?php

namespace App\Actions\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class AuthenticateUserAction
{
    public function execute(array $credentials): bool
    {
        $success = Auth::attempt($credentials);

        $this->logAttempt($credentials['email'] ?? null, $success);

        if (! $success) {
            throw ValidationException::withMessages([
                'email' => 'Credenciais inválidas.'
            ]);
        }

        request()->session()->regenerate();

        return true;
    }

    private function logAttempt(?string $email, bool $success): void
    {
        Log::info('auth_login_attempt', [
            'email' => $email,
            'success' => $success,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toISOString(),
        ]);
    }
}