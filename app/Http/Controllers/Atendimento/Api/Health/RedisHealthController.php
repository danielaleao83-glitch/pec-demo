<?php

declare(strict_types=1);

namespace App\Http\Controllers\Atendimento\Api\Health;

use Illuminate\Support\Facades\Redis;
use Throwable;

final class RedisHealthController
{
    public function check(): array
    {
        try {

            Redis::ping();

            return [

                'status'
                    => 'UP',
            ];

        } catch (Throwable) {

            return [

                'status'
                    => 'DOWN',
            ];
        }
    }
}