<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use App\Models\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class LoginService
{
    private const MAX_ATTEMPTS = 5;
    private const DECAY_SECONDS = 300;

    public function execute(Request $request): array
    {
        $this->validateRequest($request);

        $this->checkRateLimit($request);

        $user = $this->getUser($request->email);

        $this->validateCredentials($request->password, $user, $request);

        $token = $this->createToken($user);

        $sessionUuid = (string) Str::uuid();

        $this->auditLogin($request, $user, $sessionUuid);

        RateLimiter::clear($this->key($request));

        return [
            'user_id' => $user->id,
            'token' => $token,
            'session_uuid' => $sessionUuid,
        ];
    }

    private function validateRequest(Request $request): void
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);
    }

    private function getUser(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    private function validateCredentials(string $password, ?User $user, Request $request): void
    {
        if (!$user || !Hash::check($password, $user->password)) {

            RateLimiter::hit($this->key($request), self::DECAY_SECONDS);

            Log::warning('AUTH_LOGIN_FAILED', [
                'email_hash' => sha1($request->email),
                'ip' => $request->ip(),
            ]);

            throw ValidationException::withMessages([
                'email' => ['Credenciais inválidas.'],
            ]);
        }
    }

    private function createToken(User $user): string
    {
        return $user->createToken(
            'auth-' . Str::uuid()
        )->plainTextToken;
    }

    private function auditLogin(Request $request, User $user, string $sessionUuid): void
    {
        Auditoria::create([
            'user_id' => $user->id,
            'acao' => 'LOGIN',
            'modulo' => 'AUTH',
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
            'dados' => json_encode([
                'session_uuid' => $sessionUuid,
                'email_hash' => sha1($user->email),
            ]),
        ]);
    }

    private function checkRateLimit(Request $request): void
    {
        $key = $this->key($request);

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            throw ValidationException::withMessages([
                'email' => ['Muitas tentativas. Tente novamente em alguns minutos.'],
            ]);
        }
    }

    private function key(Request $request): string
    {
        return Str::lower($request->email . '|' . $request->ip());
    }
}