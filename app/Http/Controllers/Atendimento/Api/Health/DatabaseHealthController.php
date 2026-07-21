<?php

declare(strict_types=1);

namespace App\Http\Controllers\Atendimento\Api\Health;

use Illuminate\Support\Facades\DB;
use Throwable;

final class DatabaseHealthController
{
    public function check(): array
    {
        try {

            DB::connection()
                ->getPdo();

            return [

                'status'
                    => 'UP',

                'driver'
                    => config(
                        'database.default'
                    ),
            ];

        } catch (Throwable) {

            return [

                'status'
                    => 'DOWN',
            ];
        }
    }
}