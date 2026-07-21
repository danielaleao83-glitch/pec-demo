<?php

namespace App\Http\Middleware\Geo;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

use App\Services\Auditoria\AutorizacaoAuditoriaService;

class GeoIpBlockMiddleware
{
    /**
     * 🌎 Países permitidos
     */
    private array $allowedCountries = [
        'BR',
    ];

    /**
     * 🧪 IPs confiáveis (ambiente local / infra)
     */
    private array $trustedIps = [
        '127.0.0.1',
        '::1',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();

        // 🔓 bypass interno
        if (in_array($ip, $this->trustedIps)) {
            return $next($request);
        }

        // ⚡ cache evita chamadas repetidas na API externa
        $geo = Cache::remember(
            "geoip:{$ip}",
            now()->addHours(6),
            fn () => $this->consultarGeoIp($ip)
        );

        // 🧠 se falhar API, NÃO bloqueia sistema
        if (! $geo) {
            Log::warning('GeoIP indisponível', [
                'ip' => $ip,
                'rota' => $request->path(),
            ]);

            return $next($request);
        }

        $country = $geo['country_code'] ?? null;

        // 🚫 bloqueio geográfico
        if ($country && ! in_array($country, $this->allowedCountries)) {

            app(AutorizacaoAuditoriaService::class)->registrarNegado([
                'motivo' => 'PAIS_BLOQUEADO',
                'ip' => $ip,
                'pais' => $country,
                'cidade' => $geo['city'] ?? null,
                'rota' => $request->path(),
                'user_id' => $request->user()?->id,
            ]);

            Log::warning('Bloqueio GeoIP aplicado', [
                'ip' => $ip,
                'pais' => $country,
                'rota' => $request->path(),
            ]);

            return response()->json([
                'message' => 'Acesso bloqueado por política geográfica.',
                'country' => $country,
            ], 403);
        }

        return $next($request);
    }

    /**
     * 🌍 Consulta GeoIP (com proteção industrial)
     */
    private function consultarGeoIp(string $ip): ?array
    {
        try {
            $response = Http::timeout(2)
                ->retry(2, 100)
                ->get("http://ip-api.com/json/{$ip}");

            if (! $response->ok()) {
                return null;
            }

            $data = $response->json();

            if (($data['status'] ?? null) !== 'success') {
                return null;
            }

            return [
                'country_code' => $data['countryCode'] ?? null,
                'city' => $data['city'] ?? null,
                'region' => $data['regionName'] ?? null,
            ];

        } catch (\Throwable $e) {

            Log::error('Erro GeoIP service', [
                'ip' => $ip,
                'erro' => $e->getMessage(),
            ]);

            return null;
        }
    }
}