<?php

declare(strict_types=1);

namespace App\Services\ESusService\SISAB\Pipeline;

use App\Services\ESusService\SISAB\Auditoria\SisabSecurityLogService;
use App\Services\ESusService\SISAB\Metadata\SisabMetadataService;
use SimpleXMLElement;
use Throwable;
use RuntimeException;

class SisabMetadataStage
{
    /**
     * =========================================================
     * 🔐 METADATA STAGE (IMUTÁVEL / AUDITÁVEL)
     * =========================================================
     */
    public static function handle(
        SimpleXMLElement $xml,
        string $traceId,
        array $context
    ): void {

        try {

            /**
             * =====================================================
             * 🚨 GUARD: XML VÁLIDO
             * =====================================================
             */
            if (!isset($xml->Uuid)) {
                throw new RuntimeException(
                    'XML inválido: UUID não encontrado antes do metadata stage'
                );
            }

            /**
             * =====================================================
             * 🔐 GUARD: TRACE OBRIGATÓRIO
             * =====================================================
             */
            if (empty($traceId)) {
                throw new RuntimeException(
                    'TraceId obrigatório no MetadataStage'
                );
            }

            /**
             * =====================================================
             * 🧠 ADICIONA METADATA
             * =====================================================
             */
            SisabMetadataService::adicionar(
                xml: $xml,
                traceId: $traceId,
                context: $context
            );

            /**
             * =====================================================
             * 🧾 VERIFICA INTEGRIDADE DO XML APÓS MUTATION
             * =====================================================
             */
            if (!isset($xml->Meta)) {
                throw new RuntimeException(
                    'Falha ao inserir metadata no XML SISAB'
                );
            }

            /**
             * =====================================================
             * 📡 AUDITORIA DE SUCESSO
             * =====================================================
             */
            SisabSecurityLogService::info(
                'SISAB_METADATA_APPLIED',
                [
                    'trace_id' => $traceId,
                    'environment' => app()->environment(),
                    'timestamp' => now()->toIso8601String(),
                ]
            );

        } catch (Throwable $e) {

            /**
             * =====================================================
             * 🚨 FAIL SAFE
             * =====================================================
             */
            SisabSecurityLogService::critical(
                'SISAB_METADATA_FAILURE',
                [
                    'trace_id' => $traceId,
                    'error' => $e->getMessage(),
                    'exception' => get_class($e),
                    'timestamp' => now()->toIso8601String(),
                ]
            );

            throw new RuntimeException(
                'Falha no MetadataStage SISAB',
                previous: $e
            );
        }
    }
}