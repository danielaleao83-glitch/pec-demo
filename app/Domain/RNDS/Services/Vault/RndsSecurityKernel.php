<?php

namespace App\Services\ESusService\RNDS\Vault;

use Closure;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class RndsSecurityKernel
{
    /**
     * =========================================================
     * 🔐 CONFIGURAÇÕES OPERACIONAIS
     * =========================================================
     */

    /**
     * TTL replay protection
     */
    protected int $replayTtl = 300;

    /**
     * Tempo lock distribuído
     */
    protected int $lockSeconds = 5;

    /**
     * Espera máxima lock
     */
    protected int $blockSeconds = 3;

    /**
     * =========================================================
     * 🚀 EXECUÇÃO SEGURA RNDS
     * =========================================================
     */
    public function execute(Closure $callback): mixed
    {
        /**
         * 🧠 contexto operacional
         */
        $context = RndsContext::make();

        /**
         * 🧾 trace distribuído
         */
        $traceId = (string) Str::uuid();

        /**
         * 🔐 valida integridade do contexto
         */
        if (!RndsContext::validate($context)) {

            $this->security(
                'RNDS_CONTEXT_INVALID',
                $traceId,
                $context
            );

            throw new RuntimeException(
                'Contexto RNDS inválido'
            );
        }

        /**
         * 🚫 anti replay
         */
        RndsReplayGuard::check(
            $context,
            $traceId
        );

        /**
         * ⚡ circuit breaker
         */
        RndsCircuitBreaker::check(
            $context
        );

        /**
         * 🔒 chave lock distribuído
         */
        $lockKey = sprintf(
            'rnds:vault:lock:%s',
            $context['fingerprint']
        );

        try {

            /**
             * 🧾 auditoria inicial
             */
            $this->audit(
                'RNDS_KERNEL_START',
                $traceId,
                [
                    'fingerprint' => $context['fingerprint'],
                    'request_id' => $context['request_id'],
                ]
            );

            /**
             * 🔐 lock distribuído
             */
            return Cache::lock(
                $lockKey,
                $this->lockSeconds
            )->block(
                $this->blockSeconds,
                function () use (
                    $callback,
                    $context,
                    $traceId
                ) {

                    /**
                     * ⏱ início execução
                     */
                    $startedAt = microtime(true);

                    try {

                        /**
                         * 🚀 execução protegida
                         */
                        $result = $callback(
                            $context,
                            $traceId
                        );

                        /**
                         * ⏱ latência
                         */
                        $duration = round(
                            (microtime(true) - $startedAt) * 1000,
                            2
                        );

                        /**
                         * 🧾 auditoria sucesso
                         */
                        $this->audit(
                            'RNDS_KERNEL_SUCCESS',
                            $traceId,
                            [
                                'duration_ms' => $duration,
                                'fingerprint' => $context['fingerprint'],
                            ]
                        );

                        return $result;

                    } catch (Throwable $e) {

                        /**
                         * ⚡ incrementa falha
                         */
                        RndsCircuitBreaker::fail(
                            $context
                        );

                        /**
                         * 🚨 segurança
                         */
                        $this->security(
                            'RNDS_KERNEL_EXECUTION_FAILURE',
                            $traceId,
                            [
                                'error' => $e->getMessage(),
                                'type' => get_class($e),
                            ]
                        );

                        throw $e;
                    }
                }
            );

        } catch (LockTimeoutException $e) {

            /**
             * 🚨 lock timeout
             */
            $this->security(
                'RNDS_LOCK_TIMEOUT',
                $traceId,
                [
                    'lock_key' => $lockKey,
                ]
            );

            throw new RuntimeException(
                'Timeout lock RNDS'
            );

        } catch (Throwable $e) {

            /**
             * 🚨 falha global
             */
            $this->security(
                'RNDS_KERNEL_FAILURE',
                $traceId,
                [
                    'error' => $e->getMessage(),
                    'type' => get_class($e),
                ]
            );

            throw $e;
        }
    }

    /**
     * =========================================================
     * 🧾 AUDITORIA
     * =========================================================
     */
    protected function audit(
        string $event,
        string $traceId,
        array $context = []
    ): void {

        Log::channel('audit')->info(
            $event,
            array_merge(
                $context,
                [
                    'trace_id' => $traceId,
                    'timestamp' => now()->toIso8601String(),
                ]
            )
        );
    }

    /**
     * =========================================================
     * 🚨 SEGURANÇA
     * =========================================================
     */
    protected function security(
        string $event,
        string $traceId,
        array $context = []
    ): void {

        Log::channel('security')->critical(
            $event,
            array_merge(
                $context,
                [
                    'trace_id' => $traceId,
                    'timestamp' => now()->toIso8601String(),
                ]
            )
        );
    }
}