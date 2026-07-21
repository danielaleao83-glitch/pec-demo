<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class VozService
{
    public function ligar($notif)
    {
        Log::info('Ligação automática', [
            'destino' => $notif->destino,
        ]);

        return true;
    }
}
