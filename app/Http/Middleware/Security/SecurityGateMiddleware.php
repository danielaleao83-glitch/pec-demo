<?php

namespace App\Http\Middleware\Security;

use App\Services\Auditoria\AuditoriaIntelligenceService;
use App\Services\AuditoriaService;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class SecurityGateMiddleware
{
    /**
     * Score mínimo para alerta.
     */
    private const ALERT_SCORE = 50;

    /**
     * Score mínimo para bloqueio.
     */
    private const BLOCK_SCORE = 80;

    /**
     * Tempo padrão de bloqueio.
     */
    private const BLOCK_MINUTES = 30;

    /**
     * Handle request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();

        /**
         * ------------------------------------------------------------------
         * 🔒 Verifica blacklist temporária
         * ------------------------------------------------------------------
         */
        if ($this->isBlocked($ip)) {

            $this->audit(
                'security_gate_blocked',
                $request,
                [
                    'motivo' => 'IP_BLOCKED_CACHE',
                ]
            );

            return $this->deny(
                $request,
                'Acesso bloqueado temporariamente por segurança.',
                403
            );
        }

        /**
         * ------------------------------------------------------------------
         * 🧠 Inteligência comportamental
         * ------------------------------------------------------------------
         */
        try {

            $analysis = app(AuditoriaIntelligenceService::class)
                ->analisar(
                    $request->user()?->id,
                    $ip
                );

        } catch (Throwable $e) {

            Log::error('Falha SecurityGateMiddleware', [
                'erro' => $e->getMessage(),
                'ip' => $ip,
                'rota' => $request->path(),
            ]);

            $this->audit(
                'security_gate_error',
                $request,
                [
                    'erro' => $e->getMessage(),
                ]
            );

            /**
             * FAIL SAFE:
             * não bloqueia produção por falha analítica
             */
            return $next($request);
        }

        $score = (int) ($analysis['score'] ?? 0);
        $risco = $analysis['risco'] ?? 'DESCONHECIDO';

        /**
         * ------------------------------------------------------------------
         * 🚨 Bloqueio automático
         * ------------------------------------------------------------------
         */
        if ($score >= self::BLOCK_SCORE) {

            $this->blockIp($ip);

            Log::critical('SECURITY GATE BLOCK', [
                'ip' => $ip,
                'user_id' => $request->user()?->id,
                'score' => $score,
                'risco' => $risco,
                'rota' => $request->path(),
            ]);

            $this->audit(
                'security_gate_auto_block',
                $request,
                [
                    'score' => $score,
                    'risco' => $risco,
                ]
            );

            return $this->deny(
                $request,
                'Acesso bloqueado por comportamento suspeito.',
                403
            );
        }

        /**
         * ------------------------------------------------------------------
         * ⚠️ Alerta de risco elevado
         * ------------------------------------------------------------------
         */
        if ($score >= self::ALERT_SCORE) {

            Log::warning('RISCO ELEVADO DETECTADO', [
                'user_id' => $request->user()?->id,
                'ip' => $ip,
                'score' => $score,
                'risco' => $risco,
                'rota' => $request->path(),
            ]);

            $this->audit(
                'security_gate_alert',
                $request,
                [
                    'score' => $score,
                    'risco' => $risco,
                ]
            );
        }

        /**
         * ------------------------------------------------------------------
         * ✅ Request liberada
         * ------------------------------------------------------------------
         */
        return $next($request);
    }

    /**
     * ----------------------------------------------------------------------
     * 🔒 Verifica bloqueio
     * ----------------------------------------------------------------------
     */
    private function isBlocked(string $ip): bool
    {
        return Cache::has($this->blockKey($ip));
    }

    /**
     * ----------------------------------------------------------------------
     * 🚫 Bloqueia IP
     * ----------------------------------------------------------------------
     */
    private function blockIp(string $ip): void
    {
        Cache::put(
            $this->blockKey($ip),
            true,
            now()->addMinutes(self::BLOCK_MINUTES)
        );
    }

    /**
     * ----------------------------------------------------------------------
     * 🔑 Chave cache
     * ----------------------------------------------------------------------
     */
    private function blockKey(string $ip): string
    {
        return 'security:block:' . sha1($ip);
    }

    /**
     * ----------------------------------------------------------------------
     * 📊 Auditoria SUS/LGPD
     * ----------------------------------------------------------------------
     */
    private function audit(
        string $acao,
        Request $request,
        array $extra = []
    ): void {

        try {

            AuditoriaService::registrar(
                $acao,
                'security',
                null,
                $request->user()?->id,
                array_merge([
                    'ip' => $request->ip(),
                    'rota' => $request->path(),
                    'metodo' => $request->method(),
                    'user_agent' => $request->userAgent(),
                    'timestamp' => now()->toISOString(),
                ], $extra)
            );

        } catch (Throwable $e) {

            Log::error('Falha auditoria SecurityGate', [
                'erro' => $e->getMessage(),
            ]);
        }
    }

    /**
     * ----------------------------------------------------------------------
     * ❌ Resposta padronizada
     * ----------------------------------------------------------------------
     */
    private function deny(
        Request $request,
        string $message,
        int $status
    ): Response {

        if ($request->expectsJson()) {

            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], $status);
        }

        abort($status, $message);
    }
}