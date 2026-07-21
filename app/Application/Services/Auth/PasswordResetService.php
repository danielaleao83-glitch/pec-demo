<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\Auditoria;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordResetService
{
    private const MAX_ATTEMPTS = 5;
    private const DECAY_SECONDS = 60;

    /**
     * 🔐 RESET DE SENHA COMPLETO
     */
    public function reset(Request $request): array
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email:rfc,dns', 'max:255'],
            'password' => ['required', 'confirmed'],
        ]);

        $correlationId = $this->correlationId();

        $email = Str::lower(trim($validated['email']));
        $key = $this->throttleKey($request, $email);

        $this->ensureRateLimit($request, $email, $key, $correlationId);

        try {

            $status = Password::reset(
                [
                    'email' => $email,
                    'password' => $validated['password'],
                    'password_confirmation' => $request->input('password_confirmation'),
                    'token' => $validated['token'],
                ],
                function ($user) use ($validated, $request, $correlationId) {

                    $user->forceFill([
                        'password' => Hash::make($validated['password']),
                        'remember_token' => Str::random(60),
                    ])->save();

                    event(new PasswordReset($user));

                    $this->auditSuccess($user->id, $request, $correlationId);
                }
            );

            if ($status !== Password::PASSWORD_RESET) {

                $this->auditFailure($email, $request, $status, $correlationId);

                throw ValidationException::withMessages([
                    'email' => ['Não foi possível redefinir a senha.'],
                ]);
            }

            return [
                'status' => 'success',
                'message' => 'Senha redefinida com sucesso.',
                'correlation_id' => $correlationId,
            ];

        } catch (ValidationException $e) {
            throw $e;

        } catch (\Throwable $e) {

            Log::channel('security')->critical('RESET_PASSWORD_FATAL', [
                'error' => $e->getMessage(),
                'email_hash' => hash('sha256', $email),
                'correlation_id' => $correlationId,
            ]);

            return [
                'status' => 'error',
                'message' => 'Erro interno controlado',
                'correlation_id' => $correlationId,
            ];
        }
    }

    /**
     * 🚫 RATE LIMIT CHECK
     */
    private function ensureRateLimit(
        Request $request,
        string $email,
        string $key,
        string $correlationId
    ): void {
        if (!RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            return;
        }

        $seconds = RateLimiter::availableIn($key);

        Log::channel('security')->warning('RESET_PASSWORD_RATE_LIMIT', [
            'email_hash' => hash('sha256', $email),
            'ip_hash' => hash('sha256', (string) $request->ip()),
            'seconds' => $seconds,
            'correlation_id' => $correlationId,
        ]);

        throw ValidationException::withMessages([
            'email' => ["Muitas tentativas. Aguarde {$seconds} segundos."],
        ]);
    }

    /**
     * 🧠 AUDITORIA SUCESSO
     */
    private function auditSuccess(
        string $userId,
        Request $request,
        string $correlationId
    ): void {
        Auditoria::create([
            'user_id' => $userId,
            'acao' => 'password_reset_success',
            'modulo' => 'auth',
            'registro_id' => $userId,
            'dados_antes' => null,
            'dados_depois' => [
                'status' => 'success',
                'correlation_id' => $correlationId,
            ],
            'ip' => hash('sha256', (string) $request->ip()),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
            'url' => $request->fullUrl(),
            'metodo_http' => $request->method(),
            'executado_em' => now(),
            'hash_integridade' => hash('sha256', implode('|', [
                $userId,
                'password_reset_success',
                now()->timestamp,
                config('app.key'),
                $correlationId,
            ])),
        ]);
    }

    /**
     * ❌ AUDITORIA FALHA
     */
    private function auditFailure(
        string $email,
        Request $request,
        string $status,
        string $correlationId
    ): void {
        Auditoria::create([
            'user_id' => null,
            'acao' => 'password_reset_failed',
            'modulo' => 'auth',
            'registro_id' => null,
            'dados_antes' => null,
            'dados_depois' => [
                'email_hash' => hash('sha256', $email),
                'status' => $status,
                'correlation_id' => $correlationId,
            ],
            'ip' => hash('sha256', (string) $request->ip()),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
            'url' => $request->fullUrl(),
            'metodo_http' => $request->method(),
            'executado_em' => now(),
            'hash_integridade' => hash('sha256', implode('|', [
                $email,
                'password_reset_failed',
                now()->timestamp,
                config('app.key'),
                $correlationId,
            ])),
        ]);
    }

    /**
     * 🔑 THROTTLE KEY
     */
    private function throttleKey(Request $request, string $email): string
    {
        return Str::transliterate($email . '|' . $request->ip());
    }

    /**
     * 🧠 CORRELATION ID
     */
    private function correlationId(): string
    {
        return app()->bound('correlation_id')
            ? app('correlation_id')
            : (string) Str::uuid();
    }
}