<?php

declare(strict_types=1);

namespace App\Services\ESusService\SISAB\Contexto;

class SisabContextoService
{
    public static function gerar(
        string $traceId
    ): array {

        $ip = request()->ip() ?? 'CLI';

        $userAgent = substr(
            request()->userAgent() ?? 'CLI',
            0,
            120
        );

        $userUuid =
            auth()->user()?->uuid;

        $unidadeUuid =
            auth()->user()?->unidade_uuid;

        $fingerprint = hash(
            'sha256',
            implode('|', [

                $ip,

                $userAgent,

                $userUuid,

                $unidadeUuid,

                config('app.key'),
            ])
        );

        return [

            'trace_id' => $traceId,

            'ip' => $ip,

            'user_agent' => $userAgent,

            'user_uuid' => $userUuid,

            'unidade_uuid' => $unidadeUuid,

            'fingerprint' => $fingerprint,
        ];
    }
}