<?php

declare(strict_types=1);

namespace App\Services\ESusService\SISAB\Xml;

use App\Services\ESusService\SISAB\Metadata\SisabMetadataService;
use Illuminate\Support\Str;
use RuntimeException;
use SimpleXMLElement;

class SisabXmlBuilder
{
    public static function build(
        array $dados,
        string $traceId,
        array $context
    ): array {

        $xmlUuid = (string) Str::uuid();

        $xml = new SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8"?><Atendimento/>'
        );

        $xml->addChild(
            'Uuid',
            $xmlUuid
        );

        $xml->addChild(
            'PacienteUuid',
            (string) $dados['paciente_uuid']
        );

        $xml->addChild(
            'ProfissionalUuid',
            (string) $dados['profissional_uuid']
        );

        if (!empty($dados['unidade_uuid'])) {

            $xml->addChild(
                'UnidadeUuid',
                (string) $dados['unidade_uuid']
            );
        }

        $xml->addChild(
            'DataAtendimento',
            now()->toIso8601String()
        );

        $xml->addChild(
            'Descricao',
            htmlspecialchars(
                (string) (
                    $dados['descricao']
                    ?? ''
                ),
                ENT_XML1 | ENT_COMPAT,
                'UTF-8'
            )
        );

        SisabMetadataService::append(
            xml: $xml,
            traceId: $traceId,
            context: $context
        );

        $xmlString = $xml->asXML();

        if ($xmlString === false) {

            throw new RuntimeException(
                'Falha geração XML SISAB'
            );
        }

        return [

            'xml' => $xmlString,

            'xml_uuid' => $xmlUuid,
        ];
    }
}