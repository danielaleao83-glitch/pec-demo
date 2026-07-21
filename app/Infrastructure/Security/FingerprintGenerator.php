<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use Illuminate\Http\Request;

final class FingerprintGenerator
{
    public function generate(
        Request $request,
        array $payload
    ): string {

        return hash(
            'sha256',
            implode('|', [

                $request->ip(),

                $request->userAgent(),

                json_encode($payload),

                now()->timestamp,
            ])
        );
    }
}