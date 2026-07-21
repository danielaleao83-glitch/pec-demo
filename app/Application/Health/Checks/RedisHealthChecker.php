<?php

declare(strict_types=1);

namespace App\Services\Health\Checkers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Throwable;

final class RedisHealthChecker
{
    public function check(): array
    {
        $start = hrtime(true);

        /**
         * 🔐 IDENTIDADE GLOBAL DA EXECUÇÃO
         */
        $executionUuid = (string) Str::uuid();

        $instanceId = gethostname();
        $env = app()->environment();

        $key = 'healthcheck:' . $executionUuid;
        $value = bin2hex(random_bytes(8));

        try {

            /**
             * ✍️ ESCRITA
             */
            $writeStart = hrtime(true);

            $writeOk = Cache::store('redis')->put($key, $value, 5);

            $writeLatency = $this->latency($writeStart);

            /**
             * 📖 LEITURA
             */
            $readStart = hrtime(true);

            $read = Cache::store('redis')->get($key);

            $readLatency = $this->latency($readStart);

            $latency = $this->latency($start);

            $consistent = $read === $value;

            return [
                'status' => $this->status($latency, $writeOk, $consistent),

                'execution_uuid' => $executionUuid,

                'latency_ms' => $latency,
                'write_latency_ms' => $writeLatency,
                'read_latency_ms' => $readLatency,

                'write_ok' => (bool) $writeOk,
                'read_ok' => $consistent,

                'consistency' => $consistent ? 'verified' : 'broken',

                /**
                 * 🔐 FINGERPRINT FEDERAL
                 */
                'fingerprint' => [
                    'instance_id' => $instanceId,
                    'environment' => $env,
                    'driver' => 'redis',

                    'execution_hash' => $this->hash(
                        $executionUuid,
                        $instanceId,
                        $env
                    ),
                ],

                /**
                 * 🧠 CONFIABILIDADE OPERACIONAL
                 */
                'confidence' => $this->confidence($latency, $consistent),

                'checked_at' => now()->toIso8601String(),
            ];

        } catch (Throwable $e) {

            return [
                'status' => 'unhealthy',

                'execution_uuid' => $executionUuid,

                'latency_ms' => $this->latency($start),

                'error' => [
                    'type' => class_basename($e),
                    'category' => $this->classifyError($e),
                ],

                'fingerprint' => [
                    'instance_id' => $instanceId,
                    'environment' => $env,
                    'driver' => 'redis',

                    'execution_hash' => $this->hash(
                        $executionUuid,
                        $instanceId,
                        $env
                    ),
                ],

                'confidence' => 'none',

                'consistency' => 'unknown',

                'checked_at' => now()->toIso8601String(),
            ];
        }
    }

    /**
     * ⏱ latência real (alta precisão)
     */
    private function latency(int $start): float
    {
        return round((hrtime(true) - $start) / 1e6, 2);
    }

    /**
     * 🧠 estado operacional real
     */
    private function status(float $latency, bool $writeOk, bool $consistent): string
    {
        if (! $writeOk || ! $consistent) {
            return 'unhealthy';
        }

        return match (true) {
            $latency < 30  => 'healthy',
            $latency < 120 => 'degraded',
            default         => 'unhealthy',
        };
    }

    /**
     * 🧠 confiabilidade do diagnóstico
     */
    private function confidence(float $latency, bool $consistent): string
    {
        if (! $consistent) {
            return 'low';
        }

        return match (true) {
            $latency < 30  => 'high',
            $latency < 120 => 'medium',
            default         => 'low',
        };
    }

    /**
     * 🔐 hash forense determinístico
     */
    private function hash(string $uuid, string $instance, string $env): string
    {
        return hash('sha256', $uuid . '|' . $instance . '|' . $env);
    }

    /**
     * 🧠 classificação estruturada de falhas
     */
    private function classifyError(Throwable $e): string
    {
        return match (true) {
            $e instanceof \RedisException => 'redis_failure',
            default => 'unknown_failure',
        };
    }

    private function latency(int $start): float
    {
        return round((hrtime(true) - $start) / 1e6, 2);
    }
}