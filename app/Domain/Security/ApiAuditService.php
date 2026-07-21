<?php

namespace App\Services\Security;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ApiAuditService
{
    /**
     * 🚀 REGISTRO PRINCIPAL
     */
    public static function registrar(
        Request $request,
        string $acao = 'ACCESS',
        ?string $registroId = null,
        array $extra = []
    ): void {

        if (! config('app.audit.enabled', true)) {
            return;
        }

        if (self::isFlooding($request)) {
            return;
        }

        try {

            // =====================================================
            // 🧠 CONTEXTO
            // =====================================================
            $userId = Auth::id();

            $ip = self::resolverIpSeguro($request);
            $userAgent = substr((string) $request->userAgent(), 0, 500);
            $url = substr((string) $request->fullUrl(), 0, 1000);
            $metodo = $request->method();

            $correlationId = self::resolverCorrelationId($request);
            $modulo = self::resolverModulo($request);

            // =====================================================
            // 🧬 PAYLOAD (SANITIZADO LGPD)
            // =====================================================
            $payload = self::sanitizarProfundo(array_merge(
                $request->all(),
                $extra
            ));

            // =====================================================
            // 🔗 CADEIA FORENSE
            // =====================================================
            $hashAnterior = DB::table('auditorias')
                ->orderByDesc('executado_em')
                ->value('hash_integridade');

            // =====================================================
            // 🧾 DADOS
            // =====================================================
            $dados = [
                'id' => (string) Str::uuid(),
                'user_id' => $userId,

                'acao' => strtoupper($acao),
                'modulo' => $modulo,
                'registro_id' => $registroId,

                'dados_antes' => null,
                'dados_depois' => json_encode($payload, JSON_UNESCAPED_UNICODE),

                'ip' => $ip,
                'user_agent' => $userAgent,
                'url' => $url,
                'metodo_http' => $metodo,

                'executado_em' => now(),
                'correlation_id' => $correlationId,

                'hash_anterior' => $hashAnterior,
            ];

            // =====================================================
            // 🔐 HASH IMUTÁVEL (CADEIA)
            // =====================================================
            $dados['hash_integridade'] = self::gerarHashSeguro($dados);

            DB::table('auditorias')->insert($dados);

            // =====================================================
            // 📊 LOG ESTRUTURADO (OBSERVABILIDADE)
            // =====================================================
            Log::channel('audit')->info('AUDITORIA_API', [
                'acao' => $dados['acao'],
                'modulo' => $dados['modulo'],
                'user_id' => $dados['user_id'],
                'ip' => $dados['ip'],
                'correlation_id' => $dados['correlation_id'],
            ]);

        } catch (\Throwable $e) {

            Log::channel('security')->error('AUDITORIA_FALHOU', [
                'erro' => $e->getMessage(),
                'linha' => $e->getLine(),
            ]);
        }
    }

    /**
     * 🔐 HASH FORTE (CADEIA FORENSE)
     */
    protected static function gerarHashSeguro(array $dados): string
    {
        return hash('sha256', json_encode([
            'id' => $dados['id'],
            'user' => $dados['user_id'],
            'acao' => $dados['acao'],
            'modulo' => $dados['modulo'],
            'registro' => $dados['registro_id'],
            'payload' => $dados['dados_depois'],
            'data' => (string) $dados['executado_em'],
            'anterior' => $dados['hash_anterior'],
            'app_key' => config('app.key'),
        ], JSON_UNESCAPED_UNICODE));
    }

    /**
     * 🌐 IP REAL (ANTI SPOOF)
     */
    protected static function resolverIpSeguro(Request $request): ?string
    {
        $trustedProxies = config('trustedproxy.proxies', []);

        $forwarded = $request->header('X-Forwarded-For');

        if ($forwarded && in_array($request->ip(), $trustedProxies)) {
            return trim(explode(',', $forwarded)[0]);
        }

        return $request->ip();
    }

    /**
     * 🔗 CORRELATION ID (PADRÃO RNDS)
     */
    protected static function resolverCorrelationId(Request $request): string
    {
        return $request->header('X-Correlation-ID')
            ?? app()->bound('correlation_id')
                ? app('correlation_id')
                : (string) Str::uuid();
    }

    /**
     * 🧠 DETECÇÃO DE MÓDULO
     */
    protected static function resolverModulo(Request $request): string
    {
        $path = strtolower($request->path());

        return match (true) {
            str_contains($path, 'paciente') => 'paciente',
            str_contains($path, 'atendimento') => 'atendimento',
            str_contains($path, 'cnes') => 'cnes',
            str_contains($path, 'domicilio') => 'domicilio',
            str_contains($path, 'familia') => 'familia',
            str_contains($path, 'auth') => 'auth',
            default => 'api',
        };
    }

    /**
     * 🧬 SANITIZAÇÃO PROFUNDA (LGPD HARD)
     */
    protected static function sanitizarProfundo(array $dados): array
    {
        $sensíveis = [
            'cpf', 'cns', 'senha', 'password',
            'token', 'access_token', 'refresh_token',
            'authorization', 'cartao_sus',
        ];

        $limite = 1000;

        $sanitize = function ($item) use (&$sanitize, $limite) {

            if (is_array($item)) {
                return array_map($sanitize, $item);
            }

            if (is_string($item)) {

                if (strlen($item) > $limite) {
                    return substr($item, 0, $limite).'...';
                }
            }

            return $item;
        };

        $resultado = [];

        foreach ($dados as $key => $value) {

            if (in_array(strtolower($key), $sensíveis)) {
                $resultado[$key] = '***PROTEGIDO***';

                continue;
            }

            $resultado[$key] = $sanitize($value);
        }

        return $resultado;
    }

    /**
     * 🚨 ANTI FLOOD
     */
    protected static function isFlooding(Request $request): bool
    {
        static $cache = [];

        $key = $request->ip();
        $now = microtime(true);

        if (! isset($cache[$key])) {
            $cache[$key] = $now;

            return false;
        }

        if (($now - $cache[$key]) < 0.1) {
            return true;
        }

        $cache[$key] = $now;

        return false;
    }
}
