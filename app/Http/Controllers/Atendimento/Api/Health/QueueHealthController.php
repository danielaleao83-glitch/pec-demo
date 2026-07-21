<?php

declare(strict_types=1);

namespace App\Http\Controllers\Atendimento\Api\Health;

use Illuminate\Support\Facades\Queue;
use Throwable;

final class QueueHealthController
{
    public function check(): array
    {
        try {

            Queue::size();

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