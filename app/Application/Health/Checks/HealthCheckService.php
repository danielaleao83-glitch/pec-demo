<?php

declare(strict_types=1);

namespace App\Services\Health\Checkers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

final class DbHealthChecker
{
    public function check(): array
    {
        $start = hrtime(true);

        /**
         * 🔐 UUID GLOBAL (EXECUÇÃO ÚNICA)
         */
        $executionUuid = (string) Str::uuid();

        $instanceId = gethostname();
        $env = app()->environment();
        $connectionName = config('database.default');

        try {
            $connection = DB::connection();
            $driver = $connection->getDriverName();

            /**
             * 🧪 TESTE REAL DE CONSISTÊNCIA
             */
            $result = $connection->select('SELECT 1 as health_check');
            $valid = isset($result[0]->health_check) && $result[0]->health_check === 1;

            $latency = $this->latency($start);

            $payload = [
                'status' => $this->status($latency, $valid),

                'execution_uuid' => $executionUuid,

                'latency_ms' => $latency,

                'driver' => $driver,
                'database' => $connectionName,

                /**
                 * 🔐 FINGERPRINT FEDERAL (CONSISTENTE + UUID)
                 */
                'fingerprint' => [
                    'instance_id' => $instanceId,
                    'environment' => $env,
                    'connection' => $connectionName,

                    'execution_hash' => $this->hash(
                        $executionUuid,
                        $instanceId,
                        $env,
                        $connectionName
                    ),
                ],

                /**
                 * 🧠 CONFIABILIDADE OPERACIONAL
                 */
                'confidence' => $this->confidence($latency, $valid),

                /**
                 * 🔐 INTEGRIDADE DO CHECK
                 */
                'integrity' => $valid ? 'verified' : 'suspect',

                'checked_at' => now()->toIso8601String(),
            ];

            /**
             * 🔐 ASSINATURA DE INTEGRIDADE DO PAYLOAD
             */
            $payload['signature'] = $this->sign($payload);

            return $payload;

        } catch (Throwable $e) {

            $payload = [
                'status' => $this->mapException($e),

                'execution_uuid' => $executionUuid,

                'latency_ms' => $this->latency($start),

                'error' => [
                    'type' => class_basename($e),
                    'category' => $this->classify($e),
                ],

                'fingerprint' => [
                    'instance_id' => $instanceId,
                    'environment' => $env,
                    'connection' => $connectionName,

                    'execution_hash' => $this->hash(
                        $executionUuid,
                        $instanceId,
                        $env,
                        $connectionName
                    ),
                ],

                'confidence' => 'none',

                'integrity' => 'failed',

                'checked_at' => now()->toIso8601String(),
            ];

            $payload['signature'] = $this->sign($payload);

            return $payload;
        }
    }

    /**
     * ⏱ latência real
     */
    private function latency(int $start): float
    {
        return round((hrtime(true) - $start) / 1e6, 2);
    }

    /**
     * 🧠 estado operacional real
     */
    private function status(float $latency, bool $valid): string
    {
        if (! $valid) {
            return 'critical';
        }

        return match (true) {
            $latency < 30  => 'healthy',
            $latency < 80  => 'stable',
            $latency < 150 => 'degraded',
            default         => 'critical',
        };
    }

    /**
     * 🧠 confiabilidade do diagnóstico
     */
    private function confidence(float $latency, bool $valid): string
    {
        if (! $valid) {
            return 'low';
        }

        return match (true) {
            $latency < 30  => 'high',
            $latency < 80  => 'medium',
            default         => 'low',
        };
    }

    /**
     * 🔐 hash FORTE com UUID incluso (auditoria real)
     */
    private function hash(string $uuid, string $instance, string $env, string $conn): string
    {
        return hash('sha256', $uuid . '|' . $instance . '|' . $env . '|' . $conn);
    }

    /**
     * 🔐 assinatura de integridade do payload
     */
    private function sign(array $payload): string
    {
        return hash('sha512', json_encode($payload));
    }

    private function classify(Throwable $e): string
    {
        return match (true) {
            $e instanceof \PDOException => 'pdo_failure',
            $e instanceof \Illuminate\Database\QueryException => 'query_failure',
            default => 'unknown_failure',
        };
    }

    private function mapException(Throwable $e): string
    {
        return match (true) {
            $e instanceof \PDOException => 'unreachable',
            $e instanceof \Illuminate\Database\QueryException => 'unstable',
            default => 'critical',
        };
    }
}