<?php

declare(strict_types=1);

namespace App\Http\Controllers\Atendimento\Api\Health;

use Illuminate\Support\Facades\Cache;
use Throwable;

final class CacheHealthController
{
    public function check(): array
    {
        try {

            Cache::put(
                'healthcheck',
                true,
                10
            );

            return [

                'status'
                    => Cache::has(
                        'healthcheck'
                    )
                    ? 'UP'
                    : 'DOWN',
            ];

        } catch (Throwable) {

            return [

                'status'
                    => 'DOWN',
            ];
        }
    }
}