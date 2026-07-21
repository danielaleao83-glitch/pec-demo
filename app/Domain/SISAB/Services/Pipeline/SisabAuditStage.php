<?php

declare(strict_types=1);

namespace App\Services\ESusService\SISAB\Pipeline;

use App\Services\ESusService\SISAB\Auditoria\SisabAuditService;
use App\Services\ESusService\SISAB\Auditoria\SisabSecurityLogService;
use RuntimeException;
use Throwable;

class SisabAuditStage
{
    /**
     * =========================================================
     * 🧾 AUDITORIA IMUTÁVEL SISAB
     * =========================================================
     */
    public static function handle(
        array $context
    ): void {

        try {

            /**
             * =====================================================
             * 🔐 VALIDAÇÃO MÍNIMA DO CONTEXTO
             * =====================================================
             */
            self::validateContext($context);

            /**
             * =====================================================
             * 🧾 NORMALIZAÇÃO DO LOG
             * =====================================================
             */
            $normalized = self::normalize($context);

            /**
             * =====================================================
             * 📌 REGISTRO DE AUDITORIA
             * =====================================================
             */
            SisabAuditService::log(
                'SISAB_XML_GENERATED',
                $normalized
            );

        } catch (Throwable $e) {

            /**
             * =====================================================
             * 🚨 FALLBACK CRÍTICO
             * =====================================================
             */
            SisabSecurityLogService::critical(
                'SISAB_AUDIT_FAILURE',
                [
                    'error' => $e->getMessage(),
                    'exception' => get_class($e),
                    'context' => $context,
                    'timestamp' => now()->toIso8601String(),
                ]
            );

            throw new RuntimeException(
                'Falha no audit stage SISAB',
                previous: $e
            );
        }
    }

    /**
     * =========================================================
     * 🔐 VALIDAÇÃO DE CONTRATO DE AUDITORIA
     * =========================================================
     */
    private static function validateContext(array $context): void
    {
        $required = [

            'trace_id',
            'xml_uuid',
            'hash',
            'chain_hash',
            'payload_size',
            'timestamp',
        ];

        foreach ($required as $field) {

            if (!array_key_exists($field, $context)) {

                throw new RuntimeException(
                    "Campo obrigatório de auditoria ausente: {$field}"
                );
            }
        }
    }

    /**
     * =========================================================
     * 🧾 NORMALIZAÇÃO FORENSE
     * =========================================================
     */
    private static function normalize(array $context): array
    {
        return [

            'event' => 'SISAB_XML_GENERATED',

            'trace_id' => (string) $context['trace_id'],

            'xml_uuid' => (string) $context['xml_uuid'],

            'hash' => (string) $context['hash'],

            'chain_hash' => (string) $context['chain_hash'],

            'previous_hash' => $context['previous_hash'] ?? null,

            'payload_size' => (int) $context['payload_size'],

            'timestamp' => (string) $context['timestamp'],

            'environment' => app()->environment(),

            'service' => 'SISAB_PIPELINE',

            'version' => 'v1',
        ];
    }
}