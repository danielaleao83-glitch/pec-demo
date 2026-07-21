<?php

namespace App\Http\Middleware\Security;

use App\Services\AuditoriaService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class GovernmentalMiddleware
{
    /**
     * 🏛 Middleware governamental SUS/LGPD
     */
    public function handle(Request $request, Closure $next): Response
    {
        $correlationId = $this->correlationId($request);

        app()->instance('correlation_id', $correlationId);

        $user = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | 📊 CONTEXTO PADRONIZADO
        |--------------------------------------------------------------------------
        */
        $context = [
            'correlation_id' => $correlationId,

            'usuario_id' => $user?->id,
            'email' => $user?->email,

            'ip' => $request->ip(),
            'host' => $request->getHost(),

            'user_agent' => $request->userAgent(),

            'rota' => $request->path(),
            'route_name' => $request->route()?->getName(),

            'metodo' => $request->method(),

            'timestamp' => now()->toISOString(),
        ];

        /*
        |--------------------------------------------------------------------------
        | 🔐 HTTPS OBRIGATÓRIO
        |--------------------------------------------------------------------------
        */
        if (! $request->secure() && app()->environment('production')) {

            Log::warning('HTTPS obrigatório', $context);

            return response()->json([
                'message' => 'HTTPS obrigatório.',
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | 🚫 NÃO AUTENTICADO
        |--------------------------------------------------------------------------
        */
        if (! $user) {

            Log::warning('Acesso sem autenticação', $context);

            $this->audit(
                'acesso_negado',
                'auth',
                $context
            );

            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | 🔑 TOKEN EXTERNO
        |--------------------------------------------------------------------------
        */
        $externalToken = $request->header('X-External-Token');

        if ($externalToken && ! $this->validateExternalToken($externalToken)) {

            Log::warning('Token externo inválido', $context);

            $this->audit(
                'token_invalido',
                'seguranca',
                $context
            );

            return response()->json([
                'message' => 'Invalid external token',
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | 🚀 EXECUTA REQUEST
        |--------------------------------------------------------------------------
        */
        $response = $next($request);

        /*
        |--------------------------------------------------------------------------
        | 🛡 SECURITY HEADERS (NÍVEL GOV)
        |--------------------------------------------------------------------------
        */
        $this->applySecurityHeaders($response);

        /*
        |--------------------------------------------------------------------------
        | 📊 AUDITORIA
        |--------------------------------------------------------------------------
        */
        try {

            $this->audit(
                'acesso_autorizado',
                'route',
                array_merge($context, [

                    // 🔐 hash leve do payload
                    'payload_hash' => hash(
                        'sha256',
                        json_encode(
                            $this->sanitizePayload($request->all()),
                            JSON_UNESCAPED_UNICODE
                        )
                    ),

                    'status_code' => $response->getStatusCode(),
                ])
            );

        } catch (\Throwable $e) {

            Log::error('Falha auditoria governamental', [
                'erro' => $e->getMessage(),
                'correlation_id' => $correlationId,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 🔗 CORRELATION HEADER
        |--------------------------------------------------------------------------
        */
        $response->headers->set(
            'X-Correlation-ID',
            $correlationId
        );

        return $response;
    }

    /**
     * 🔐 Validação segura de token
     */
    private function validateExternalToken(string $token): bool
    {
        $tokens = array_filter([
            env('EXTERNAL_API_TOKEN_1'),
            env('EXTERNAL_API_TOKEN_2'),
        ]);

        foreach ($tokens as $validToken) {

            if (hash_equals($validToken, $token)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 🧾 Auditoria protegida
     */
    private function audit(
        string $acao,
        string $modulo,
        array $context
    ): void {

        $cacheKey = 'gov_audit:'.md5(
            $acao.'|'.($context['usuario_id'] ?? 'guest').'|'.$context['rota']
        );

        // ⚡ anti flood
        if (! Cache::add($cacheKey, true, now()->addSeconds(2))) {
            return;
        }

        AuditoriaService::registrar(
            $acao,
            $modulo,
            null,
            $context['usuario_id'] ?? null,
            $context
        );
    }

    /**
     * 🧹 Sanitiza payload LGPD
     */
    private function sanitizePayload(array $payload): array
    {
        $sensitive = [
            'password',
            'password_confirmation',
            'token',
            'authorization',
            'cpf',
            'cns',
        ];

        foreach ($sensitive as $field) {

            if (isset($payload[$field])) {
                $payload[$field] = '[REDACTED]';
            }
        }

        return $payload;
    }

    /**
     * 🛡 Headers modernos
     */
    private function applySecurityHeaders(Response $response): void
    {
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
            'camera=(), microphone=(), geolocation=()'
        );

        $response->headers->set(
            'Strict-Transport-Security',
            'max-age=63072000; includeSubDomains; preload'
        );

        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; object-src 'none'; frame-ancestors 'none';"
        );
    }

    /**
     * 🔗 Correlation ID distribuído
     */
    private function correlationId(Request $request): string
    {
        return $request->header('X-Correlation-ID')
            ?? (string) Str::uuid();
    }
}