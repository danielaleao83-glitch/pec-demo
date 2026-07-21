<?php

declare(strict_types=1);

namespace App\Services\ESusService\SISAB;

use App\Services\ESusService\SISAB\DTO\SisabXmlResult;
use App\Services\ESusService\SISAB\Pipeline\SisabPipeline;
use InvalidArgumentException;

class GeradorXmlService
{
    /**
     * 🚀 ORQUESTRADOR PRINCIPAL SISAB
     *
     * Responsabilidade única:
     * → delegar execução ao pipeline
     */
    public function gerar(array $dados): SisabXmlResult
    {
        $this->validateInput($dados);

        return SisabPipeline::processar($dados);
    }

    /**
     * 🔐 validação mínima de entrada (gatekeeper)
     */
    private function validateInput(array $dados): void
    {
        if (empty($dados)) {
            throw new InvalidArgumentException(
                'SISAB: payload vazio não permitido'
            );
        }

        if (!isset($dados['paciente_uuid'])) {
            throw new InvalidArgumentException(
                'SISAB: paciente_uuid obrigatório'
            );
        }

        if (!isset($dados['profissional_uuid'])) {
            throw new InvalidArgumentException(
                'SISAB: profissional_uuid obrigatório'
            );
        }
    }
}