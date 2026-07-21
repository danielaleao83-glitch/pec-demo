<?php

declare(strict_types=1);

namespace App\Services\ESusService\SISAB\Metadata;

use SimpleXMLElement;

class SisabMetadataService
{
    public static function append(
        SimpleXMLElement $xml,
        string $traceId,
        array $context
    ): void {

        $meta = $xml->addChild(
            'Meta'
        );

        $meta->addChild(
            'TraceId',
            $traceId
        );

        $meta->addChild(
            'Environment',
            app()->environment()
        );

        $meta->addChild(
            'Fingerprint',
            $context['fingerprint']
        );

        $meta->addChild(
            'GeneratedAt',
            now()->toIso8601String()
        );
    }
}