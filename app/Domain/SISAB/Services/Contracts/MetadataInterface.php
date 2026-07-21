<?php

declare(strict_types=1);

namespace App\Services\ESusService\SISAB\Contracts;

use SimpleXMLElement;

interface MetadataInterface
{
    public static function adicionar(
        SimpleXMLElement $xml,
        string $traceId,
        array $context
    ): void;
}