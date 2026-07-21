<<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use App\Services\Auditoria\AuditoriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class ApiAuthService
{
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const DECAY_SECONDS = 60;

    public function __construct(
        private readonly AuditoriaService $auditoriaService,
    ) {
    }

    /**
     * 🔐 LOGIN
     */
    public function login(
        Request $request
    ): array {

        $this->ensureIsNotRateLimited($request);

        $validated = $request->validated();

        $correlationId = (string) Str::uuid();

        try {

            $user = User::query()
                ->where(
                    'email',
                    $validated['email']
                )
                ->first();

            if (
                !$user ||
                !Hash::check(
                    $validated['password'],
                    $user->password
                )
            ) {

                RateLimiter::hit(
                    $this->throttleKey($request),
                    self::DECAY_SECONDS
                );

                Log::warning('AUTH_LOGIN_FAILED', [
                    'email_hash' => hash(
                        'sha256',
                        $validated['email']
                    ),
                    'ip' => $request->ip(),
                    'correlation_id' => $correlationId,
                ]);

                throw ValidationException::withMessages([
                    'email' => [
                        'Credenciais inválidas.',
                    ],
                ]);
            }

            Auth::login($user);

            $request->session()->regenerate();

            $token = $user->createToken(
                'api_token_' . Str::uuid()
            )->plainTextToken;

            RateLimiter::clear(
                $this->throttleKey($request)
            );

            $this->auditoriaService->registrar(
                acao: 'AUTH_LOGIN_SUCCESS',
                modulo: 'AUTH',
                registroId: $user->id,
                userId: $user->id,
                dados: [
                    'ip' => $request->ip(),
                    'user_agent' => substr(
                        (string) $request->userAgent(),
                        0,
                        500
                    ),
                    'correlation_id' => $correlationId,
                ]
            );

            return [
                'status' => 'success',
                'message' => 'Login realizado com sucesso.',
                'data' => [
                    'token' => $token,
                    'token_type' => 'Bearer',
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                ],
                'meta' => [
                    'correlation_id' => $correlationId,
                ],
            ];

        } catch (ValidationException $e) {

            throw $e;

        } catch (Throwable $e) {

            Log::critical('AUTH_LOGIN_ERROR', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'correlation_id' => $correlationId,
            ]);

            return [
                'status' => 'error',
                'message' => 'Erro interno de autenticação.',
                'meta' => [
                    'correlation_id' => $correlationId,
                ],
            ];
        }
    }

    /**
     * 🚪 LOGOUT
     */
    public function logout(
        Request $request
    ): array {

        $correlationId = (string) Str::uuid();

        try {

            $user = $request->user();

            if (!$user) {

                return [
                    'status' => 'error',
                    'message' => 'Usuário não autenticado.',
                    'meta' => [
                        'correlation_id' => $correlationId,
                    ],
                ];
            }

            $user->currentAccessToken()?->delete();

            $request->session()->invalidate();

            $request->session()->regenerateToken();

            Auth::logout();

            $this->auditoriaService->registrar(
                acao: 'AUTH_LOGOUT_SUCCESS',
                modulo: 'AUTH',
                registroId: $user->id,
                userId: $user->id,
                dados: [
                    'ip' => $request->ip(),
                    'correlation_id' => $correlationId,
                ]
            );

            return [
                'status' => 'success',
                'message' => 'Logout realizado com sucesso.',
                'meta' => [
                    'correlation_id' => $correlationId,
                ],
            ];

        } catch (Throwable $e) {

            Log::critical('AUTH_LOGOUT_ERROR', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'correlation_id' => $correlationId,
            ]);

            return [
                'status' => 'error',
                'message' => 'Erro ao realizar logout.',
                'meta' => [
                    'correlation_id' => $correlationId,
                ],
            ];
        }
    }

    /**
     * 🚫 RATE LIMIT
     */
    private function ensureIsNotRateLimited(
        Request $request
    ): void {

        $key = $this->throttleKey($request);

        if (
            !RateLimiter::tooManyAttempts(
                $key,
                self::MAX_LOGIN_ATTEMPTS
            )
        ) {
            return;
        }

        $seconds = RateLimiter::availableIn($key);

        throw ValidationException::withMessages([
            'email' => [
                "Muitas tentativas. Aguarde {$seconds} segundos.",
            ],
        ]);
    }

    /**
     * 🔑 THROTTLE KEY
     */
    private function throttleKey(
        Request $request
    ): string {

        return Str::transliterate(
            mb_strtolower(
                (string) $request->input('email')
            ) . '|' . $request->ip()
        );
    }
}