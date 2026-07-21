<<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\Auditoria;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class AuthService
{
    private const MAX_ATTEMPTS = 5;
    private const LOCK_SECONDS = 300;

    /**
     * 🏥 LOGIN FEDERAL SUS (NÍVEL 10)
     */
    public function login(Request $request): array
    {
        $key = $this->throttleKey($request);
        $this->ensureNotRateLimited($key);

        $validated = $request->validate([
            'email' => ['required', 'email:rfc,dns'],
            'password' => ['required', 'string'],
            'unidade_id' => ['nullable', 'uuid'],
            'device_id' => ['nullable', 'string'], // 🧠 rastreio dispositivo SUS
        ]);

        $email = Str::lower($validated['email']);

        $user = User::query()
            ->where('email', $email)
            ->first();

        /**
         * ❌ LOGIN INVÁLIDO
         */
        if (!$user || !Hash::check($validated['password'], $user->password)) {

            RateLimiter::hit($key, self::LOCK_SECONDS);

            Log::channel('security')->warning('AUTH_FAIL', [
                'email_hash' => sha1($email),
                'ip' => $request->ip(),
                'device_id' => $validated['device_id'] ?? null,
                'ua' => substr((string) $request->userAgent(), 0, 200),
            ]);

            throw ValidationException::withMessages([
                'email' => ['Credenciais inválidas'],
            ]);
        }

        /**
         * 🚫 BLOQUEIO GLOBAL
         */
        if ($user->blocked) {
            throw ValidationException::withMessages([
                'email' => ['Usuário bloqueado administrativamente'],
            ]);
        }

        /**
         * 🏥 VALIDAÇÃO CNES / UNIDADE REAL
         */
        if (!$this->validateUnitAccess($user, $validated['unidade_id'] ?? null)) {
            Log::channel('security')->warning('AUTH_UNIT_DENIED', [
                'user_id' => $user->id,
                'unidade_id' => $validated['unidade_id'],
            ]);

            throw ValidationException::withMessages([
                'email' => ['Acesso negado à unidade de saúde'],
            ]);
        }

        return DB::transaction(function () use ($user, $request, $validated, $key) {

            Auth::login($user);
            $request->session()?->regenerate();

            RateLimiter::clear($key);

            $token = method_exists($user, 'createToken')
                ? $user->createToken('SUS-' . Str::uuid())->plainTextToken
                : null;

            /**
             * 🧠 ATUALIZA CONTEXTO CLÍNICO
             */
            $user->update([
                'last_login_at' => now(),
                'last_ip' => $request->ip(),
                'last_device_id' => $validated['device_id'] ?? null,
                'unidade_id' => $validated['unidade_id'] ?? $user->unidade_id,
            ]);

            /**
             * 🧾 EVENTO SUS (PRONTO PARA RNDS)
             */
            event(new \App\Events\Auth\UserLoggedIn([
                'user_id' => $user->id,
                'unidade_id' => $user->unidade_id,
                'device_id' => $validated['device_id'] ?? null,
                'ip' => $request->ip(),
                'timestamp' => now()->toIso8601String(),
            ]));

            /**
             * 🔐 AUDITORIA IMUTÁVEL (hash encadeado estilo blockchain leve)
             */
            $this->auditImmutable($request, $user, 'login', $validated);

            return [
                'user' => $user,
                'token' => $token,
            ];
        });
    }

    /**
     * 🚪 LOGOUT FEDERAL
     */
    public function logout(Request $request): void
    {
        $user = $request->user();

        if (!$user) return;

        $user->tokens()->delete();
        Auth::logout();

        $request->session()?->invalidate();
        $request->session()?->regenerateToken();

        $this->auditImmutable($request, $user, 'logout');
    }

    /**
     * 🧠 AUDITORIA IMUTÁVEL (SUS LEVEL)
     */
    private function auditImmutable(Request $request, User $user, string $action, array $extra = []): void
    {
        $payload = [
            'user_id' => $user->id,
            'acao' => $action,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toIso8601String(),
            'extra' => $extra,
        ];

        $previousHash = Auditoria::latest('id')->value('hash_integridade');

        $hash = hash('sha256', json_encode($payload) . $previousHash);

        Auditoria::create([
            'user_id' => $user->id,
            'acao' => $action,
            'modulo' => 'auth',
            'dados_depois' => $payload,
            'hash_integridade' => $hash,
        ]);
    }

    /**
     * 🏥 REGRA CNES REAL (AGORA SÉRIA)
     */
    private function validateUnitAccess(User $user, ?string $unidadeId): bool
    {
        if (!$unidadeId) return true;

        return DB::table('user_unidades')
            ->where('user_id', $user->id)
            ->where('unidade_id', $unidadeId)
            ->exists();
    }

    /**
     * 🚫 RATE LIMIT
     */
    private function ensureNotRateLimited(string $key): void
    {
        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            throw ValidationException::withMessages([
                'email' => ['Muitas tentativas. Aguarde.'],
            ]);
        }
    }

    private function throttleKey(Request $request): string
    {
        return Str::lower($request->input('email') . '|' . $request->ip());
    }
}