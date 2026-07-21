<?php

declare(strict_types=1);

namespace App\Services\ESusService\SISAB\Factories;

use Illuminate\Support\Str;

class SisabContextFactory
{
    public static function make(): array
    {
        $traceId = (string) Str::uuid();

        $ip = request()->ip() ?? 'CLI';

        $userAgent = substr(
            request()->userAgent() ?? 'CLI',
            0,
            255
        );

        $userUuid =
            auth()->user()?->uuid;

        return [

            'trace_id' => $traceId,

            'ip' => $ip,

            'user_agent' => $userAgent,

            'user_uuid' => $userUuid,

            'environment' =>
                app()->environment(),

            'fingerprint' => hash(
                'sha256',
                implode('|', [

                    $ip,

                    $userAgent,

                    $userUuid,

                    config('app.key'),
                ])
            ),
        ];
    }
}