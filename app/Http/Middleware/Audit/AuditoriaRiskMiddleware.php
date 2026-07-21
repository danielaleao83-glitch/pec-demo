<?php

namespace App\Http\Middleware\Audit;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Services\Auditoria\AuditoriaIntelligenceService;
use App\Services\Auditoria\AutorizacaoAuditoriaService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AuditoriaRiskMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $ip = $request->ip();

        if ($this->isRotaPublica($request)) {
            return $next($request);
        }

        if ($this->isAdmin($user)) {
            return $next($request);
        }

        if ($this->ipBloqueado($ip)) {
            return $this->bloqueio($request, $user, $ip, 'IP_BLACKLIST');
        }

        $pais = $this->detectarPaisSeguro($ip);

        if ($this->paisBloqueado($pais)) {
            return $this->bloqueio($request, $user, $ip, 'PAIS_BLOQUEADO', $pais);
        }

        $intelligence = app(AuditoriaIntelligenceService::class);

        $resultado = $intelligence->analisar($user?->id, $ip);

        $risco = $resultado['risco'] ?? 'BAIXO';
        $score = $resultado['score'] ?? 0;

        if (in_array($risco, ['ALTO', 'CRÍTICO'])) {
            return $this->bloqueio($request, $user, $ip, 'RISCO_ELEVADO', $pais, $score, $risco);
        }

        $this->registrarPermitido($user, $ip, $request, $pais, $score, $risco);

        return $next($request);
    }

    /**
     * 🚨 bloqueio centralizado
     */
    private function bloqueio(Request $request, $user, string $ip, string $motivo, $pais = null, $score = null, $risco = null)
    {
        app(AutorizacaoAuditoriaService::class)->registrarNegado([
            'motivo' => $motivo,
            'ip' => $ip,
            'user_id' => $user?->id,
            'rota' => $request->path(),
            'pais' => $pais,
            'score' => $score,
            'risco' => $risco,
        ]);

        return response()->json([
            'message' => 'Acesso bloqueado.',
            'motivo' => $motivo,
        ], 403);
    }

    /**
     * 📊 registro permitido com proteção contra flood
     */
    private function registrarPermitido($user, string $ip, Request $request, $pais, $score, $risco): void
    {
        $key = "audit:allow:{$ip}";

        if (!Cache::add($key, true, 10)) {
            return; // evita flood de logs
        }

        app(AutorizacaoAuditoriaService::class)->registrar([
            'status' => 'PERMITIDO',
            'score' => $score,
            'risco' => $risco,
            'user_id' => $user?->id,
            'ip' => $ip,
            'rota' => $request->path(),
            'pais' => $pais,
        ]);
    }

    /**
     * 🌍 geolocalização resiliente
     */
    private function detectarPaisSeguro(string $ip): ?string
    {
        return Cache::remember("geo:{$ip}", 3600, function () use ($ip) {

            try {
                $response = Http::timeout(1.5)
                    ->retry(1, 200)
                    ->get("https://ip-api.com/json/{$ip}");

                if (! $response->ok()) {
                    return null;
                }

                return $response->json()['countryCode'] ?? null;

            } catch (\Throwable $e) {
                Log::warning('GeoIP falhou', [
                    'ip' => $ip,
                    'erro' => $e->getMessage(),
                ]);

                return null;
            }
        });
    }

    /**
     * 👑 admin seguro (RBAC ready)
     */
    private function isAdmin($user): bool
    {
        return $user?->roles?->contains('name', 'admin')
            || $user?->role === 'admin';
    }

    private function isRotaPublica(Request $request): bool
    {
        return $request->is([
            'login',
            'logout',
            'password/*',
            'sanctum/*',
            'api/public/*',
        ]);
    }

    private function ipBloqueado(string $ip): bool
    {
        return in_array($ip, ['1.2.3.4', '5.6.7.8']);
    }

    private function paisBloqueado(?string $pais): bool
    {
        return in_array($pais, ['RU', 'CN', 'KP']);
    }
}