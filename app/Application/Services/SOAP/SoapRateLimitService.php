<?php

declare(strict_types=1);

namespace App\Application\Services\SOAP;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

final class SoapRateLimitService
{
    private const MAX_REQUESTS = 60;

    public function handle(
        Request $request
    ): void {

        $key = 'soap_rate_' . sha1(
            $request->ip()
        );

        $attempts = Cache::get($key, 0);

        if ($attempts >= self::MAX_REQUESTS) {

            abort(
                Response::HTTP_TOO_MANY_REQUESTS,
                'Rate limit.'
            );
        }

        Cache::put(
            $key,
            $attempts + 1,
            60
        );
    }
}