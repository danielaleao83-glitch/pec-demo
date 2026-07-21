<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthRNDSService
{
    private const MAX_ATTEMPTS = 5;
    private const LOCK_SECONDS = 300;

    /**
     * 🔐 AUTENTICAÇÃO RNDS (CNS + EMAIL + UNIDADE)
     */
    public function authenticate(Request $request): array
    {
        $key = $this->throttleKey($request);

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            throw ValidationException::withMessages([
                'email' => ['Bloqueio temporário por segurança RNDS.'],
            ]);
        }

        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'cns' => ['nullable', 'string'], // 🏥 Cartão SUS
            'cnes' => ['nullable', 'string'], // 🏥 unidade
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            RateLimiter::hit($key, self::LOCK_SECONDS);
            throw ValidationException::withMessages([
                'auth' => ['Credenciais inválidas RNDS.'],
            ]);
        }

        /**
         * 🏥 validação de unidade (CNES)
         */
        if (!empty($data['cnes']) && !$this->validateCNES($user, $data['cnes'])) {
            throw ValidationException::withMessages([
                'cnes' => ['Unidade não autorizada.'],
            ]);
        }

        RateLimiter::clear($key);

        Auth::login($user);
        $request->session()?->regenerate();

        $token = method_exists($user, 'createToken')
            ? $user->createToken('rnds_' . Str::uuid())->plainTextToken
            : null;

        return [
            'user' => $this->identity($request, $user),
            'token' => $token,
            'session_id' => $request->session()?->getId(),
        ];
    }

    /**
     * 👤 IDENTIDADE FEDERAL RNDS
     */
    public function identity(Request $request, ?User $user = null): array
    {
        $user = $user ?? $request->user();

        return [
            'uuid' => $user?->uuid,
            'id' => $user?->id,
            'name' => $user?->name,
            'email' => $user?->email,

            // 🏥 CAMADA RNDS
            'cns' => $user?->cns ?? null,
            'cnes' => $user?->unidade_id ?? null,
            'role' => $user?->role ?? 'professional',

            // 🔐 CONTEXTO
            'session_id' => $request->session()?->getId(),
            'ip' => $request->ip(),

            // 🧠 FEDERAL TRACE
            'trace_id' => (string) Str::uuid(),
        ];
    }

    /**
     * 🚪 LOGOUT RNDS
     */
    public function logout(Request $request): void
    {
        $user = $request->user();

        if ($user) {
            $user->currentAccessToken()?->delete();
        }

        Auth::logout();

        $request->session()?->invalidate();
        $request->session()?->regenerateToken();
    }

    /**
     * 🏥 VALIDA CNES (placeholder integração SUS real)
     */
    private function validateCNES(User $user, string $cnes): bool
    {
        // aqui entraria integração real CNES / base federal
        return true;
    }

    /**
     * 🔑 throttle key
     */
    private function throttleKey(Request $request): string
    {
        return Str::lower($request->input('email') . '|' . $request->ip());
    }
}