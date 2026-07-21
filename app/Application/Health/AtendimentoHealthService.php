<?php

declare(strict_types=1);

namespace App\Services\Health;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class AtendimentoHealthService
{
    public function run(): array
    {
        $startedAt = microtime(true);

        $correlationId = app('correlation_id') ?? null;

        $components = [
            'database' => $this->safeCheck(fn () => $this->checkDatabase()),
            'cache'    => $this->safeCheck(fn () => $this->checkCache()),
            'redis'    => $this->safeCheck(fn () => $this->checkRedis()),
            'storage'  => $this->safeCheck(fn () => $this->checkStorage()),
        ];

        $metrics = $this->calculateMetrics($components);

        $status = $this->calculateSystemStatus($metrics);

        return [
            'status' => $status,
            'system' => 'atendimento_core',

            'components' => $components,

            'metrics' => array_merge($metrics, [
                'total_latency_ms' => round((microtime(true) - $startedAt) * 1000, 2),
            ]),

            'correlation_id' => $correlationId,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * 🔐 ISOLAMENTO TOTAL DE FALHAS
     */
    private function safeCheck(callable $check): array
    {
        try {
            return $check();
        } catch (Throwable $e) {
            return [
                'status' => 'failed',
                'error' => [
                    'type' => class_basename($e),
                    'message' => $e->getMessage(),
                ],
                'latency_ms' => null,
            ];
        }
    }

    /**
     * 🗄 DATABASE
     */
    private function checkDatabase(): array
    {
        $start = microtime(true);

        DB::select('SELECT 1');

        return [
            'status' => 'healthy',
            'latency_ms' => round((microtime(true) - $start) * 1000, 2),
        ];
    }

    /**
     * ⚡ CACHE
     */
    private function checkCache(): array
    {
        $start = microtime(true);

        Cache::put('healthcheck:key', true, 5);
        $ok = Cache::get('healthcheck:key') === true;

        return [
            'status' => $ok ? 'healthy' : 'degraded',
            'latency_ms' => round((microtime(true) - $start) * 1000, 2),
        ];
    }

    /**
     * 🧠 REDIS
     */
    private function checkRedis(): array
    {
        $start = microtime(true);

        Redis::ping();

        return [
            'status' => 'healthy',
            'latency_ms' => round((microtime(true) - $start) * 1000, 2),
        ];
    }

    /**
     * 💾 STORAGE
     */
    private function checkStorage(): array
    {
        $start = microtime(true);

        Storage::disk('local')->exists('/');

        return [
            'status' => 'healthy',
            'latency_ms' => round((microtime(true) - $start) * 1000, 2),
        ];
    }

    /**
     * 📊 MÉTRICAS GLOBAIS
     */
    private function calculateMetrics(array $components): array
    {
        $failed = 0;
        $degraded = 0;

        foreach ($components as $component) {
            match ($component['status'] ?? null) {
                'failed' => $failed++,
                'degraded' => $degraded++,
                default => null,
            };
        }

        return [
            'components_total' => count($components),
            'components_failed' => $failed,
            'components_degraded' => $degraded,
        ];
    }

    /**
     * 🧠 DECISÃO GLOBAL REAL
     */
    private function calculateSystemStatus(array $metrics): string
    {
        return match (true) {
            $metrics['components_failed'] >= 1 => 'critical',
            $metrics['components_degraded'] >= 2 => 'degraded',
            default => 'healthy',
        };
    }
}