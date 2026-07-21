<?php

declare(strict_types=1);

namespace App\Http\Middleware\Security;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ClinicalRateLimitMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $key = $this->throttleKey($request);

        // Limite clínico defensivo
        if (RateLimiter::tooManyAttempts($key, 10)) {

            throw ValidationException::withMessages([
                'erro' => [
                    'Muitas tentativas. Aguarde '
                    . RateLimiter::availableIn($key)
                    . ' segundos.'
                ],
            ]);
        }

        // janela de 60 segundos
        RateLimiter::hit($key, 60);

        return $next($request);
    }

    /**
     * 🔐 Chave anti abuso
     */
    private function throttleKey(Request $request): string
    {
        return Str::lower(
            (string) (Auth::id() ?? 'guest')
            .'|'.$request->ip()
            .'|'.$request->path()
        );
    }
}