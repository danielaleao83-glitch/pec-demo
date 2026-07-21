<?php

declare(strict_types=1);

namespace App\Services\ESusService\SISAB\Contracts;

use App\Services\ESusService\SISAB\DTO\SisabXmlResult;

interface XmlGeneratorInterface
{
    public function gerar(
        array $dados
    ): SisabXmlResult;
}