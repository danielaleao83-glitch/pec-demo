<?php

declare(strict_types=1);

namespace App\Services\ESusService\SISAB\Contracts;

use SimpleXMLElement;

interface XmlWriterInterface
{
    public static function criar(
        array $payload
    ): SimpleXMLElement;
}