<?php

namespace App\Http\Middleware\Security;

use App\Models\Auditoria\Auditoria;
use App\Models\LogForense;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class SecurityMiddleware
{
    /**
     * Tempo máximo permitido entre request e servidor.
     */
    private const MAX_REQUEST_AGE = 300;

    /**
     * TTL anti replay.
     */
    private const REPLAY_TTL = 5;

    /**
     * ----------------------------------------------------------------------
     * 🔐 Middleware principal
     * ----------------------------------------------------------------------
     */
    public function handle(Request $request, Closure $next): Response
    {
        $start = microtime(true);

        $user = Auth::user();

        /**
         * ------------------------------------------------------------------
         * 🔒 Autenticação obrigatória
         * ------------------------------------------------------------------
         */
        if (! $user) {
            return $this->deny('Não autenticado.', 401);
        }

        $ip = $request->ip();

        /**
         * ------------------------------------------------------------------
         * 🧠 Correlation ID distribuído
         * ------------------------------------------------------------------
         */
        $correlationId = $request->header('X-Correlation-ID')
            ?? (string) Str::uuid();

        app()->instance('correlation_id', $correlationId);

        /**
         * ------------------------------------------------------------------
         * ⏱ Proteção replay attack
         * ------------------------------------------------------------------
         */
        $timestamp = (int) $request->header('X-Timestamp');

        if (! $timestamp) {

            $this->auditSecurity(
                'missing_timestamp',
                $request,
                $correlationId
            );

            return $this->deny(
                'Timestamp obrigatório.',
                403
            );
        }

        if (abs(time() - $timestamp) > self::MAX_REQUEST_AGE) {

            $this->auditSecurity(
                'expired_request',
                $request,
                $correlationId
            );

            return $this->deny(
                'Requisição expirada.',
                403
            );
        }

        /**
         * ------------------------------------------------------------------
         * 🔐 Anti replay cache
         * ------------------------------------------------------------------
         */
        $replayKey = $this->replayKey(
            $user->id,
            $correlationId,
            $timestamp
        );

        if (! Cache::add($replayKey, true, now()->addMinutes(self::REPLAY_TTL))) {

            $this->auditSecurity(
                'replay_attack_detected',
                $request,
                $correlationId
            );

            return $this->deny(
                'Replay attack detectado.',
                403
            );
        }

        /**
         * ------------------------------------------------------------------
         * 🔐 Assinatura HMAC
         * ------------------------------------------------------------------
         */
        $signature = $request->header('X-Signature');

        if (! $signature) {

            $this->auditSecurity(
                'missing_signature',
                $request,
                $correlationId
            );

            return $this->deny(
                'Assinatura ausente.',
                403
            );
        }

        $payload = $request->getContent();

        $expectedSignature = hash_hmac(
            'sha256',
            $payload . $timestamp,
            config('security.hmac_secret')
        );

        if (! hash_equals($expectedSignature, $signature)) {

            $this->auditSecurity(
                'invalid_signature',
                $request,
                $correlationId
            );

            return $this->deny(
                'Assinatura inválida.',
                403
            );
        }

        /**
         * ------------------------------------------------------------------
         * 🚨 Payload malicioso
         * ------------------------------------------------------------------
         */
        if ($this->detectMaliciousInput($request)) {

            Log::warning('Payload suspeito detectado', [
                'user_id' => $user->id,
                'ip' => $ip,
                'correlation_id' => $correlationId,
            ]);

            $this->auditSecurity(
                'malicious_payload',
                $request,
                $correlationId
            );

            return $this->deny(
                'Payload suspeito.',
                403
            );
        }

        /**
         * ------------------------------------------------------------------
         * 📊 Contexto forense
         * ------------------------------------------------------------------
         */
        $context = [
            'user_id' => $user->id,
            'email' => $user->email,
            'rota' => $request->path(),
            'metodo' => $request->method(),
            'ip' => $ip,
            'host' => $request->getHost(),
            'user_agent' => $request->userAgent(),
            'correlation_id' => $correlationId,
            'timestamp' => now()->toISOString(),
        ];

        /**
         * ------------------------------------------------------------------
         * 📋 Auditoria principal
         * ------------------------------------------------------------------
         */
        try {

            Auditoria::registrarForense(
                'ACCESS',
                $this->fakeModel($request),
                null,
                $context
            );

        } catch (Throwable $e) {

            Log::critical('Falha auditoria forense', [
                'erro' => $e->getMessage(),
                'correlation_id' => $correlationId,
            ]);
        }

        /**
         * ------------------------------------------------------------------
         * 🧬 Log clínico
         * ------------------------------------------------------------------
         */
        try {

            $pacienteId = $request->route('paciente')
                ?? $request->input('paciente_id')
                ?? $request->route('id');

            if ($pacienteId) {

                LogForense::create([
                    'id' => (string) Str::uuid(),

                    'user_id' => $user->id,
                    'paciente_id' => $pacienteId,

                    'acao' => 'ACESSO_PACIENTE',

                    'rota' => $request->path(),
                    'metodo' => $request->method(),

                    'ip' => $ip,
                    'user_agent' => $request->userAgent(),

                    'correlation_id' => $correlationId,

                    'hash_integridade' => $this->gerarHashForense([
                        $user->id,
                        $pacienteId,
                        $request->path(),
                        $timestamp,
                    ]),
                ]);
            }

        } catch (Throwable $e) {

            Log::critical('Falha log forense paciente', [
                'erro' => $e->getMessage(),
                'correlation_id' => $correlationId,
            ]);
        }

        /**
         * ------------------------------------------------------------------
         * ▶ Executa request
         * ------------------------------------------------------------------
         */
        $response = $next($request);

        /**
         * ------------------------------------------------------------------
         * 🔐 Security headers hospitalares
         * ------------------------------------------------------------------
         */
        $this->applySecurityHeaders(
            $response,
            $correlationId
        );

        /**
         * ------------------------------------------------------------------
         * 📈 Monitoramento de performance
         * ------------------------------------------------------------------
         */
        $duration = round(
            (microtime(true) - $start) * 1000,
            2
        );

        Log::channel('monitoring')->info('REQUEST_MONITOR', [
            'rota' => $request->path(),
            'metodo' => $request->method(),
            'tempo_ms' => $duration,
            'status' => $response->getStatusCode(),
            'user_id' => $user->id,
            'correlation_id' => $correlationId,
        ]);

        return $response;
    }

    /**
     * ----------------------------------------------------------------------
     * 🚨 Detecção básica de ataque
     * ----------------------------------------------------------------------
     */
    private function detectMaliciousInput(Request $request): bool
    {
        $payload = strtolower(
            json_encode(
                $request->all(),
                JSON_UNESCAPED_UNICODE
            )
        );

        return (bool) preg_match(
            '/(<script|union\s+select|drop\s+table|--|sleep\(|benchmark\(|onerror=|onload=)/i',
            $payload
        );
    }

    /**
     * ----------------------------------------------------------------------
     * 🔐 Hash forense
     * ----------------------------------------------------------------------
     */
    private function gerarHashForense(array $dados): string
    {
        return hash(
            'sha256',
            json_encode([
                ...$dados,
                config('app.key'),
            ], JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * ----------------------------------------------------------------------
     * 🔑 Chave replay
     * ----------------------------------------------------------------------
     */
    private function replayKey(
        string $userId,
        string $correlationId,
        int $timestamp
    ): string {
        return 'security:replay:' . sha1(
            $userId . '|' . $correlationId . '|' . $timestamp
        );
    }

    /**
     * ----------------------------------------------------------------------
     * 🔐 Security headers
     * ----------------------------------------------------------------------
     */
    private function applySecurityHeaders(
        Response $response,
        string $correlationId
    ): void {

        $response->headers->set(
            'X-Correlation-ID',
            $correlationId
        );

        $response->headers->set(
            'X-Frame-Options',
            'DENY'
        );

        $response->headers->set(
            'X-Content-Type-Options',
            'nosniff'
        );

        $response->headers->set(
            'Referrer-Policy',
            'strict-origin'
        );

        $response->headers->set(
            'Permissions-Policy',
            'geolocation=(), microphone=(), camera=()'
        );

        $response->headers->set(
            'Strict-Transport-Security',
            'max-age=31536000; includeSubDomains; preload'
        );

        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; object-src 'none'; frame-ancestors 'none'; base-uri 'self';"
        );
    }

    /**
     * ----------------------------------------------------------------------
     * 📊 Auditoria de segurança
     * ----------------------------------------------------------------------
     */
    private function auditSecurity(
        string $acao,
        Request $request,
        string $correlationId
    ): void {

        try {

            Auditoria::create([
                'user_id' => $request->user()?->id,

                'acao' => $acao,
                'modulo' => 'security',

                'registro_id' => null,

                'dados_depois' => [
                    'rota' => $request->path(),
                    'metodo' => $request->method(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'correlation_id' => $correlationId,
                ],

                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'metodo_http' => $request->method(),

                'executado_em' => now(),

                'hash_integridade' => hash(
                    'sha256',
                    $acao . '|' . $request->ip() . '|' . $correlationId
                ),
            ]);

        } catch (Throwable $e) {

            Log::error('Falha auditSecurity', [
                'erro' => $e->getMessage(),
            ]);
        }
    }

    /**
     * ----------------------------------------------------------------------
     * ❌ Response deny
     * ----------------------------------------------------------------------
     */
    private function deny(
        string $message,
        int $status
    ): JsonResponse {

        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }

    /**
     * ----------------------------------------------------------------------
     * 🧪 Fake model auditoria
     * ----------------------------------------------------------------------
     */
    private function fakeModel(Request $request): object
    {
        return new class($request)
        {
            public function __construct(
                private readonly Request $request
            ) {}

            public function getKey(): string
            {
                return $this->request->path();
            }
        };
    }
}