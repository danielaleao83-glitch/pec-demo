<?php

declare(strict_types=1);

namespace App\Services\ESusService\SISAB\Pipeline;

use App\Services\ESusService\SISAB\Auditoria\SisabSecurityLogService;
use App\Services\ESusService\SISAB\Canonicalizacao\SisabCanonicalizacaoService;
use App\Services\ESusService\SISAB\Integridade\SisabIntegridadeService;
use RuntimeException;
use Throwable;

class SisabIntegrityStage
{
    /**
     * =========================================================
     * 🔐 INTEGRIDADE SISAB (FORA DE ALTERAÇÃO)
     * =========================================================
     */
    public static function handle(
        string $xmlString,
        string $traceId
    ): array {

        try {

            /**
             * =====================================================
             * 🚨 VALIDACAO DE ENTRADA
             * =====================================================
             */
            if (trim($xmlString) === '') {
                throw new RuntimeException(
                    'XML vazio na etapa de integridade'
                );
            }

            if (empty($traceId)) {
                throw new RuntimeException(
                    'TraceId obrigatório na integridade'
                );
            }

            /**
             * =====================================================
             * 🧬 CANONICALIZAÇÃO
             * =====================================================
             */
            $canonical =
                SisabCanonicalizacaoService::canonicalizar(
                    $xmlString
                );

            if (trim($canonical) === '') {
                throw new RuntimeException(
                    'Falha na canonicalização SISAB'
                );
            }

            /**
             * =====================================================
             * 🔐 HASH PRINCIPAL
             * =====================================================
             */
            $hash = hash(
                'sha512',
                $canonical
            );

            if ($hash === false || $hash === '') {
                throw new RuntimeException(
                    'Falha geração hash SISAB'
                );
            }

            /**
             * =====================================================
             * 🔗 CHAIN HASH (IMUTABILIDADE HISTÓRICA)
             * =====================================================
             */
            $chain =
                SisabIntegridadeService::gerarChainHash(
                    $hash,
                    $traceId
                );

            /**
             * =====================================================
             * 🧪 CONSISTÊNCIA INTERNA
             * =====================================================
             */
            if (
                empty($chain['chain_hash']) ||
                empty($chain['previous_hash'])
            ) {
                throw new RuntimeException(
                    'Falha na cadeia de integridade SISAB'
                );
            }

            /**
             * =====================================================
             * 🚨 AUDITORIA DE INTEGRIDADE
             * =====================================================
             */
            SisabSecurityLogService::info(
                'SISAB_INTEGRITY_OK',
                [
                    'trace_id' => $traceId,
                    'hash' => $hash,
                    'chain_hash' => $chain['chain_hash'],
                    'timestamp' => now()->toIso8601String(),
                ]
            );

            /**
             * =====================================================
             * 🚀 RESULTADO FINAL
             * =====================================================
             */
            return [
                'canonical' => $canonical,
                'hash' => $hash,
                'chain_hash' => $chain['chain_hash'],
                'previous_hash' => $chain['previous_hash'],
            ];

        } catch (Throwable $e) {

            /**
             * =====================================================
             * 🚨 LOG DE FALHA CRÍTICA
             * =====================================================
             */
            SisabSecurityLogService::critical(
                'SISAB_INTEGRITY_FAILURE',
                [
                    'trace_id' => $traceId,
                    'error' => $e->getMessage(),
                    'exception' => get_class($e),
                    'timestamp' => now()->toIso8601String(),
                ]
            );

            throw new RuntimeException(
                'Falha na integridade SISAB',
                previous: $e
            );
        }
    }
}