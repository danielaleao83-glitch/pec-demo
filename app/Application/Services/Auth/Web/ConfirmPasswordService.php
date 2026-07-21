<?php

declare(strict_types=1);

namespace App\Services\Auth\Web;

use App\Models\Auditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ConfirmPasswordService
{
    private const PASSWORD_CONFIRM_TIMEOUT = 1800;

    public function confirm(
        Request $request
    ): RedirectResponse {

        $user = $request->user();

        $correlationId = (string) Str::uuid();

        if (! $user) {
            abort(401);
        }

        try {

            $valid = Auth::guard('web')->validate([
                'email' => $user->email,
                'password' => (string) $request->input('password'),
            ]);

            if (! $valid) {

                $this->audit(
                    action: 'confirm_password_failed',
                    request: $request,
                    userId: (string) $user->id,
                    correlationId: $correlationId,
                    data: [
                        'status' => 'failed',
                    ]
                );

                Log::warning(
                    'CONFIRMACAO_SENHA_FALHOU',
                    [
                        'user_id' => $user->id,
                        'ip_hash' => hash(
                            'sha256',
                            (string) $request->ip()
                        ),
                        'correlation_id' => $correlationId,
                    ]
                );

                throw ValidationException::withMessages([
                    'password' => [
                        __('auth.password'),
                    ],
                ]);
            }

            $request->session()->put(
                'auth.password_confirmed_at',
                time()
            );

            $request->session()->put(
                'auth.password_confirm_timeout',
                self::PASSWORD_CONFIRM_TIMEOUT
            );

            $request->session()->regenerate();

            $this->audit(
                action: 'confirm_password_success',
                request: $request,
                userId: (string) $user->id,
                correlationId: $correlationId,
                data: [
                    'status' => 'confirmed',
                ]
            );

            Log::info(
                'CONFIRMACAO_SENHA_SUCESSO',
                [
                    'user_id' => $user->id,
                    'correlation_id' => $correlationId,
                ]
            );

            return redirect()->intended(
                route(
                    'dashboard',
                    absolute: false
                )
            );

        } catch (ValidationException $e) {

            throw $e;

        } catch (\Throwable $e) {

            Log::critical(
                'CONFIRMACAO_SENHA_ERRO',
                [
                    'erro' => $e->getMessage(),
                    'user_id' => $user?->id,
                    'correlation_id' => $correlationId,
                ]
            );

            abort(
                500,
                'Erro interno controlado'
            );
        }
    }

    /**
     * 🧾 Auditoria
     */
    private function audit(
        string $action,
        Request $request,
        ?string $userId,
        string $correlationId,
        array $data = []
    ): void {

        Auditoria::query()->create([

            'user_id' => $userId,

            'acao' => $action,

            'modulo' => 'auth',

            'registro_id' => $userId,

            'dados_antes' => null,

            'dados_depois' => array_merge([
                'ip_hash' => hash(
                    'sha256',
                    (string) $request->ip()
                ),

                'user_agent' => substr(
                    (string) $request->userAgent(),
                    0,
                    500
                ),

                'correlation_id' => $correlationId,

            ], $data),

            'ip' => hash(
                'sha256',
                (string) $request->ip()
            ),

            'user_agent' => substr(
                (string) $request->userAgent(),
                0,
                500
            ),

            'url' => $request->fullUrl(),

            'metodo_http' => $request->method(),

            'executado_em' => now(),

            'hash_integridade' => hash(
                'sha256',
                implode('|', [
                    $userId ?? 'guest',
                    $action,
                    now()->timestamp,
                    $request->ip(),
                    $correlationId,
                    config('app.key'),
                ])
            ),
        ]);
    }
}