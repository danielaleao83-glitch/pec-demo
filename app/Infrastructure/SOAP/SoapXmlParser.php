<?php

declare(strict_types=1);

namespace App\Infrastructure\SOAP;

use RuntimeException;

final class SoapXmlParser
{
    public function parse(
        string $xml
    ): array {

        libxml_disable_entity_loader(true);

        $previous =
            libxml_use_internal_errors(true);

        try {

            $soap = simplexml_load_string(
                $xml,
                'SimpleXMLElement',
                LIBXML_NONET
                | LIBXML_NOCDATA
                | LIBXML_NOBLANKS
            );

            if (! $soap) {
                throw new RuntimeException(
                    'SOAP inválido.'
                );
            }

            return json_decode(
                json_encode($soap),
                true
            );

        } finally {

            libxml_clear_errors();

            libxml_use_internal_errors($previous);
        }
    }
}