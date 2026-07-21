<?php

declare(strict_types=1);

namespace App\Application\Services\SOAP;

use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

final class SoapReplayProtectionService
{
    private const TTL = 300;

    public function ensureNotReplay(
        string $hash
    ): void {

        $key = 'soap_replay_' . $hash;

        if (Cache::has($key)) {

            abort(
                Response::HTTP_CONFLICT,
                'Replay detectado.'
            );
        }

        Cache::put(
            $key,
            true,
            self::TTL
        );
    }
}