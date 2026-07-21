<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SmsService
{
    public function enviar($notif)
    {
        Log::info('Enviando SMS', [
            'destino' => $notif->destino,
            'mensagem' => $notif->mensagem,
        ]);

        return true;
    }
}
