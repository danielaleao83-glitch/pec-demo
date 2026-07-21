<?php

declare(strict_types=1);

namespace App\Services\ESusService\SISAB\Canonicalizacao;

use DOMDocument;
use RuntimeException;

class SisabCanonicalizacaoService
{
    public static function canonicalizar(
        string $xml
    ): string {

        $dom = new DOMDocument();

        $dom->preserveWhiteSpace = false;

        $dom->formatOutput = false;

        $loaded = $dom->loadXML(
            $xml,
            LIBXML_NOBLANKS
            | LIBXML_NOCDATA
            | LIBXML_NONET
            | LIBXML_NOERROR
            | LIBXML_NOWARNING
        );

        if (!$loaded) {

            throw new RuntimeException(
                'Falha canonicalização XML SISAB'
            );
        }

        $canonical = $dom->C14N();

        if ($canonical === false) {

            throw new RuntimeException(
                'Falha geração XML canônico'
            );
        }

        return $canonical;
    }
}