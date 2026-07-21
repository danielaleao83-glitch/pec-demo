<?php

declare(strict_types=1);

namespace App\Infrastructure\Integrations\CNES;

use Illuminate\Support\Facades\Http;

/**
 * =========================================================
 * 🏥 CNES CLIENT
 * =========================================================
 *
 * ✔ HTTP seguro
 * ✔ Timeout
 * ✔ Retry
 * ✔ Produção federal
 *
 * =========================================================
 */
final class CNESClient
{
    public function consultar(
        string $cnes
    ): array {

        $response = Http::timeout(15)
            ->retry(3, 500)
            ->acceptJson()
            ->get(
                config('services.cnes.url')
                . '/unidades/' . $cnes
            );

        if ($response->failed()) {

            throw new \RuntimeException(
                'Falha integração CNES.'
            );
        }

        return $response->json();
    }
}