<?php

declare(strict_types=1);

namespace App\Services\ESusService\SISAB\Pipeline;

use App\Services\ESusService\SISAB\Xml\SisabXmlWriterService;
use SimpleXMLElement;
use RuntimeException;

class SisabXmlBuildStage
{
    /**
     * 🚀 constrói XML com validação estrutural SISAB
     */
    public static function handle(array $payload): SimpleXMLElement
    {
        self::validatePayload($payload);

        $xml = SisabXmlWriterService::criar($payload);

        self::validateXml($xml, $payload);

        return $xml;
    }

    /**
     * 🔐 valida payload mínimo obrigatório SISAB
     */
    private static function validatePayload(array $payload): void
    {
        $required = [
            'paciente_uuid',
            'profissional_uuid',
        ];

        foreach ($required as $field) {

            if (!isset($payload[$field])) {
                throw new RuntimeException(
                    "SISAB XMLBuildStage: campo ausente ({$field})"
                );
            }

            $value = trim((string) $payload[$field]);

            if ($value === '') {
                throw new RuntimeException(
                    "SISAB XMLBuildStage: campo vazio ({$field})"
                );
            }

            if (!self::isUuid($value)) {
                throw new RuntimeException(
                    "SISAB XMLBuildStage: UUID inválido ({$field})"
                );
            }
        }
    }

    /**
     * 🔐 valida XML gerado
     */
    private static function validateXml(SimpleXMLElement $xml, array $payload): void
    {
        $xmlString = $xml->asXML();

        if ($xmlString === false) {
            throw new RuntimeException('Falha ao serializar XML SISAB');
        }

        /**
         * 🔐 valida root obrigatório
         */
        if (!str_contains($xmlString, '<Atendimento')) {
            throw new RuntimeException('XML SISAB sem root <Atendimento>');
        }

        /**
         * 🔐 valida encoding
         */
        if (!str_contains($xmlString, 'UTF-8')) {
            throw new RuntimeException('XML SISAB sem encoding UTF-8');
        }

        /**
         * 🔐 valida consistência mínima com payload
         */
        foreach (['paciente_uuid', 'profissional_uuid'] as $field) {
            if (!str_contains($xmlString, (string) $payload[$field])) {
                throw new RuntimeException(
                    "XML SISAB inconsistente: {$field} não encontrado no XML"
                );
            }
        }

        /**
         * 🔐 proteção contra XML truncado
         */
        if (strlen($xmlString) < 30) {
            throw new RuntimeException('XML SISAB inválido ou corrompido');
        }
    }

    /**
     * 🔐 valida UUID padrão RFC
     */
    private static function isUuid(string $value): bool
    {
        return (bool) preg_match(
            '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[1-5][0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}$/',
            $value
        );
    }
}