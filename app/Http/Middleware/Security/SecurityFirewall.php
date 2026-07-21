<?php

declare(strict_types=1);

namespace App\Http\Middleware\Security;

use App\Services\AuditoriaService;
use App\Services\SecurityMonitor;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class SecurityFirewall
{
    /**
     * 🔥 limite por minuto
     */
    private int $limiteRequisicoes = 120;

    /**
     * 🔥 bloqueio inicial
     */
    private int $tempoBloqueio = 10;

    /**
     * 🛡 scanners conhecidos
     */
    private array $bots = [
        'sqlmap',
        'nikto',
        'masscan',
        'nmap',
        'acunetix',
        'nessus',
    ];

    public function handle(
        Request $request,
        Closure $next
    ): Response {

        $ip = $request->ip();

        $userAgent = Str::lower(
            substr(
                $request->userAgent() ?? 'unknown',
                0,
                500
            )
        );

        $traceId = app()->bound('correlation_id')
            ? app('correlation_id')
            : Str::uuid()->toString();

        $context = [
            'trace_id' => $traceId,
            'ip' => $ip,
            'rota' => $request->path(),
            'metodo' => $request->method(),
            'user_agent' => $userAgent,
            'timestamp' => now()->toISOString(),
        ];

        $reqKey = "fw:req:{$ip}";
        $blockKey = "fw:block:{$ip}";
        $blacklistKey = "fw:blacklist:{$ip}";

        try {

            /*
            |--------------------------------------------------------------------------
            | 🚫 BLACKLIST
            |--------------------------------------------------------------------------
            */
            if (Cache::has($blacklistKey)) {

                $this->audit(
                    'firewall_blacklist',
                    $context
                );

                return $this->bloquear(
                    'IP permanentemente bloqueado',
                    403
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 🚫 BLOCK TEMP
            |--------------------------------------------------------------------------
            */
            if (Cache::has($blockKey)) {

                return $this->bloquear(
                    'Acesso temporariamente bloqueado',
                    403
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 📊 CONTROLE REQUEST
            |--------------------------------------------------------------------------
            */
            if (! Cache::has($reqKey)) {

                Cache::put(
                    $reqKey,
                    0,
                    now()->addMinute()
                );
            }

            $contador = Cache::increment($reqKey);

            /*
            |--------------------------------------------------------------------------
            | 🚨 FLOOD
            |--------------------------------------------------------------------------
            */
            if ($contador > $this->limiteRequisicoes) {

                $tempo = match (true) {

                    $contador > 1000 => 1440,
                    $contador > 500 => 120,
                    $contador > 300 => 60,

                    default => $this->tempoBloqueio,
                };

                if ($contador > 1000) {

                    Cache::put(
                        $blacklistKey,
                        true,
                        now()->addDay()
                    );
                }

                Cache::put(
                    $blockKey,
                    true,
                    now()->addMinutes($tempo)
                );

                Log::critical(
                    'Flood detectado',
                    array_merge($context, [
                        'requests' => $contador,
                        'tempo_bloqueio' => $tempo,
                    ])
                );

                $this->audit(
                    'firewall_flood',
                    $context
                );

                return $this->bloquear(
                    'Muitas requisições detectadas',
                    429
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 🤖 SCANNER
            |--------------------------------------------------------------------------
            */
            foreach ($this->bots as $bot) {

                if (str_contains($userAgent, $bot)) {

                    Cache::put(
                        $blockKey,
                        true,
                        now()->addMinutes(60)
                    );

                    Log::warning(
                        'Scanner detectado',
                        $context
                    );

                    $this->audit(
                        'firewall_scanner',
                        $context
                    );

                    return $this->bloquear(
                        'Scanner detectado',
                        403
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | 🧠 PAYLOAD INSPECTION
            |--------------------------------------------------------------------------
            */
            if (
                $request->isMethod('POST')
                || $request->isMethod('PUT')
                || $request->isMethod('PATCH')
            ) {

                if ($this->payloadMalicioso($request)) {

                    Cache::put(
                        $blockKey,
                        true,
                        now()->addMinutes(30)
                    );

                    Log::critical(
                        'Payload suspeito',
                        $context
                    );

                    $this->audit(
                        'firewall_payload',
                        $context
                    );

                    return $this->bloquear(
                        'Payload bloqueado',
                        403
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | 🛰 SECURITY MONITOR
            |--------------------------------------------------------------------------
            */
            try {

                SecurityMonitor::verificarAcessoSuspeito(
                    null,
                    $request
                );

            } catch (Throwable $e) {

                Log::error(
                    'Erro SecurityMonitor',
                    [
                        'trace_id' => $traceId,
                        'erro' => $e->getMessage(),
                    ]
                );
            }

            $response = $next($request);

            $response->headers->set(
                'X-Firewall',
                'ACTIVE'
            );

            return $response;

        } catch (Throwable $e) {

            Log::critical(
                'Falha crítica SecurityFirewall',
                [
                    'trace_id' => $traceId,
                    'erro' => $e->getMessage(),
                ]
            );

            /**
             * FAIL SAFE
             * não derruba aplicação
             */
            return $next($request);
        }
    }

    /**
     * 🧠 inspeção segura
     */
    private function payloadMalicioso(
        Request $request
    ): bool {

        $payload = json_encode(
            $this->sanitize($request->all()),
            JSON_UNESCAPED_UNICODE
        );

        if (! $payload) {
            return false;
        }

        /**
         * 🔥 limita payload
         */
        $payload = Str::lower(
            substr($payload, 0, 5000)
        );

        $patterns = [
            '/union\s+select/i',
            '/drop\s+table/i',
            '/<script/i',
            '/onerror\s*=/i',
            '/or\s+1=1/i',
            '/sleep\s*\(/i',
            '/benchmark\s*\(/i',
        ];

        foreach ($patterns as $pattern) {

            if (preg_match($pattern, $payload)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 🔐 sanitização LGPD
     */
    private function sanitize(
        array $payload
    ): array {

        $hidden = [
            'password',
            'token',
            'authorization',
            'cpf',
            'cns',
        ];

        foreach ($hidden as $field) {

            if (isset($payload[$field])) {

                $payload[$field] =
                    '[REDACTED]';
            }
        }

        return $payload;
    }

    /**
     * 📊 auditoria protegida
     */
    private function audit(
        string $acao,
        array $context
    ): void {

        try {

            AuditoriaService::registrar(
                $acao,
                'seguranca',
                null,
                null,
                $context
            );

        } catch (Throwable $e) {

            Log::error(
                'Falha auditoria firewall',
                [
                    'erro' => $e->getMessage(),
                ]
            );
        }
    }

    /**
     * 🚫 resposta protegida
     */
    private function bloquear(
        string $mensagem,
        int $codigo
    ): Response {

        return request()->expectsJson()

            ? response()->json([
                'status' => 'blocked',
                'message' => $mensagem,
            ], $codigo)

            : abort($codigo, $mensagem);
    }
}