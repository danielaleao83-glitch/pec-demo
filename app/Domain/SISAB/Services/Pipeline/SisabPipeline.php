<?php

declare(strict_types=1);

namespace App\Services\ESusService\SISAB\Pipeline;

use App\Services\ESusService\SISAB\DTO\SisabXmlResult;
use App\Services\ESusService\SISAB\Factories\SisabContextFactory;
use App\Services\ESusService\SISAB\Factories\SisabXmlFactory;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class SisabPipeline
{
    /**
     * 🚀 PIPELINE PRINCIPAL SISAB (BLOQUEADO)
     */
    public static function processar(array $dados): SisabXmlResult
    {
        try {

            /**
             * =====================================================
             * 🧠 CONTEXTO GLOBAL
             * =====================================================
             */
            $context = SisabContextFactory::make();

            $traceId = $context['trace_id'];

            /**
             * =====================================================
             * 🔐 VALIDATION STAGE
             * =====================================================
             */
            $payload = SisabValidationStage::handle($dados);

            /**
             * =====================================================
             * 🧼 SANITIZATION STAGE
             * =====================================================
             */
            $payload = SisabSanitizationStage::handle($payload);

            /**
             * =====================================================
             * 🧬 XML UUID
             * =====================================================
             */
            $xmlUuid = (string) Str::uuid();

            /**
             * =====================================================
             * 🧠 TRANSFORMATION STAGE
             * =====================================================
             */
            $payload = SisabTransformationStage::handle(
                $payload,
                $xmlUuid,
                $context
            );

            /**
             * =====================================================
             * 🧾 XML BUILD STAGE
             * =====================================================
             */
            $xml = SisabXmlBuildStage::handle($payload);

            /**
             * =====================================================
             * 🚨 CONSISTENCY GUARD (NOVO)
             * =====================================================
             */
            if (!$xml instanceof \SimpleXMLElement) {
                throw new RuntimeException('XML inválido gerado pelo pipeline');
            }

            /**
             * =====================================================
             * 🧬 STRING XML
             * =====================================================
             */
            $xmlString = $xml->asXML();

            if ($xmlString === false) {
                throw new RuntimeException('Falha geração XML SISAB');
            }

            /**
             * =====================================================
             * 🔐 INTEGRITY STAGE
             * =====================================================
             */
            $integrity = SisabIntegrityStage::handle(
                $xmlString,
                $traceId
            );

            /**
             * =====================================================
             * 🧾 AUDIT STAGE
             * =====================================================
             */
            SisabAuditStage::handle([
                'trace_id' => $traceId,
                'xml_uuid' => $xmlUuid,
                'hash' => $integrity['hash'],
                'chain_hash' => $integrity['chain_hash'],
                'previous_hash' => $integrity['previous_hash'],
                'payload_size' => strlen($xmlString),
                'timestamp' => now()->toIso8601String(),
            ]);

            /**
             * =====================================================
             * 🚀 RESULT FACTORY
             * =====================================================
             */
            return SisabXmlFactory::makeResult(
                status: true,
                xml: $xmlString,
                hash: $integrity['hash'],
                chainHash: $integrity['chain_hash'],
                traceId: $traceId,
                canonical: $integrity['canonical'],
                meta: [
                    'xml_uuid' => $xmlUuid,
                    'payload_size' => strlen($xmlString),
                    'environment' => app()->environment(),
                ]
            );

        } catch (Throwable $e) {

            /**
             * =====================================================
             * 🚨 FAIL SAFE GLOBAL
             * =====================================================
             */
            SisabSecurityLogStage::handle([
                'error' => $e->getMessage(),
                'trace' => $traceId ?? null,
                'exception' => get_class($e),
                'timestamp' => now()->toIso8601String(),
            ]);

            throw new RuntimeException(
                'Falha crítica no pipeline SISAB',
                previous: $e
            );
        }
    }
}