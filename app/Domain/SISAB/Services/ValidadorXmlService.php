<?php

declare(strict_types=1);

namespace App\Services\ESusService\SISAB;

use Exception;
use SimpleXMLElement;

class ValidadorXmlService
{
    /**
     * 🚀 validação estrutural SISAB (XML real, não string)
     */
    public function validar(string $xml): void
    {
        $this->validateNotEmpty($xml);

        $xmlObject = $this->parseXml($xml);

        $this->validateStructure($xmlObject);
    }

    /**
     * 🔐 garante que XML não está vazio/corrompido
     */
    private function validateNotEmpty(string $xml): void
    {
        if (trim($xml) === '') {
            throw new Exception('XML SISAB vazio');
        }

        if (strlen($xml) < 20) {
            throw new Exception('XML SISAB inválido ou corrompido');
        }
    }

    /**
     * 🧬 converte para XML estruturado
     */
    private function parseXml(string $xml): SimpleXMLElement
    {
        libxml_use_internal_errors(true);

        $object = simplexml_load_string($xml);

        if ($object === false) {
            throw new Exception('XML SISAB malformado');
        }

        return $object;
    }

    /**
     * 🔐 valida estrutura clínica obrigatória
     */
    private function validateStructure(SimpleXMLElement $xml): void
    {
        if (!$this->hasNode($xml, 'Paciente')) {
            throw new Exception('XML inválido: nó Paciente ausente');
        }

        if (!$this->hasNode($xml, 'Profissional')) {
            throw new Exception('XML inválido: nó Profissional ausente');
        }
    }

    /**
     * 🧠 busca segura de nós XML
     */
    private function hasNode(SimpleXMLElement $xml, string $node): bool
    {
        return count($xml->xpath("//*[local-name()='{$node}']")) > 0;
    }
}