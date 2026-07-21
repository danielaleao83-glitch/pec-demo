<?php

declare(strict_types=1);

namespace App\Services\ESusService\SISAB\Factories;

use App\Services\ESusService\SISAB\DTO\SisabXmlResult;

class SisabXmlFactory
{
    public static function makeResult(
        bool $status,
        string $xml,
        string $hash,
        string $chainHash,
        string $traceId,
        string $canonical,
        array $meta = []
    ): SisabXmlResult {

        return new SisabXmlResult(

            status: $status,

            xml: $xml,

            hash: $hash,

            chainHash: $chainHash,

            traceId: $traceId,

            canonical: $canonical,

            meta: $meta
        );
    }
}