<?php

declare(strict_types=1);

namespace App\Http\Controllers\Atendimento\Api\Health;

use Illuminate\Support\Facades\Storage;
use Throwable;

final class StorageHealthController
{
    public function check(): array
    {
        try {

            Storage::disk('local')
                ->exists('/');

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