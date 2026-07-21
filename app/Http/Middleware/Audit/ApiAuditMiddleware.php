<?php

namespace App\Http\Middleware\Audit;

use App\Services\Security\ApiAuditService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Throwable;

class ApiAuditMiddleware
{
    /**
     * 🏥 Middleware de auditoria API (nível federal SUS)
     */
    public function handle(Request $request, Closure $next)
    {
        // 🚫 proteção básica contra abuso de payload
        if ($request->getContent() && strlen($request->getContent()) > 200000) {
            return response()->json(['message' => 'Payload muito grande'], 413);
        }

        $correlationId = $this->generateCorrelationId($request);
        app()->instance('correlation_id', $correlationId);

        $start = microtime(true);
        $user = Auth::user();

        // 🧠 rate limit leve por IP (proteção audit flood)
        $this->rateLimit($request);

        $base = $this->baseContext($request, $user, $correlationId);

        try {

            $response = $next($request);
            $status = $response->getStatusCode();

        } catch (Throwable $e) {

            $status = 500;

            $this->audit($request, $status, $start, $base, [
                'erro' => substr($e->getMessage(), 0, 500),
                'exception' => class_basename($e),
                'rota' => $request->route()?->getName(),
            ]);

            Log::critical('API FATAL ERROR SUS', [
                'correlation_id' => $correlationId,
                'user_id' => $user?->id,
                'erro' => substr($e->getMessage(), 0, 500),
            ]);

            throw $e;
        }

        $this->audit($request, $status, $start, $base);

        return $response->header('X-Correlation-ID', $correlationId);
    }

    /**
     * 📊 auditoria central
     */
    protected function audit(Request $request, int $status, float $start, array $base, array $extra = [])
    {
        $duration = round((microtime(true) - $start) * 1000, 2);

        $payload = array_merge($base, [
            'status_http' => $status,
            'tempo_ms' => $duration,
            'metodo' => $request->method(),
            'rota' => $request->route()?->getName(),
            'uri' => $request->path(),
        ], $extra);

        try {
            // 🛡 fail-safe: auditoria nunca pode quebrar request
            if (class_exists(ApiAuditService::class)) {
                ApiAuditService::registrar(
                    $request,
                    'API_REQUEST',
                    Auth::id(),
                    $payload
                );
            }

        } catch (Throwable $e) {

            Log::warning('Falha auditoria API SUS', [
                'erro' => $e->getMessage(),
                'correlation_id' => $base['correlation_id'] ?? null,
            ]);
        }
    }

    /**
     * 📊 contexto base seguro
     */
    protected function baseContext(Request $request, $user, string $correlationId): array
    {
        return [
            'correlation_id' => $correlationId,
            'usuario_id' => $user?->id,
            'email' => $user?->email,
            'roles' => $user?->roles?->pluck('name')->toArray() ?? [],
            'role_legacy' => $user?->role?->name ?? null,
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
            'host' => $request->getHost(),
        ];
    }

    /**
     * 🔗 correlation id
     */
    protected function generateCorrelationId(Request $request): string
    {
        return $request->header('X-Correlation-ID')
            ?? (string) Str::uuid();
    }

    /**
     * 🧯 proteção contra flood de auditoria
     */
    protected function rateLimit(Request $request): void
    {
        $key = 'api_audit:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 200)) {
            abort(429, 'Muitas requisições');
        }

        RateLimiter::hit($key, 60);
    }
}