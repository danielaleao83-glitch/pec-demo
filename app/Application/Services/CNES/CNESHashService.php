<?php

declare(strict_types=1);

namespace App\Application\Services\CNES;

final class CNESHashService
{
    public function generate(
        string $cnes,
        string $correlationId,
        string $fingerprint
    ): string {

        return hash(
            'sha512',
            implode('|', [

                $cnes,

                $correlationId,

                $fingerprint,

                now()->timestamp,

                config('app.key'),
            ])
        );
    }
}