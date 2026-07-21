<?php

namespace App\Services;

use App\Jobs\ProcessarWebhookJob;
use Illuminate\Support\Facades\Log;

class IntegracaoService
{
    /*
    |--------------------------------------------------------------------------
    | 📡 ENVIAR PARA SISTEMAS EXTERNOS (SUS READY)
    |--------------------------------------------------------------------------
    */
    public function enviar(string $url, array $payload): void
    {
        ProcessarWebhookJob::dispatch($url, $payload);

        Log::info('WEBHOOK ENFILEIRADO', [
            'url' => $url,
            'payload' => $payload,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 🏥 SISAB
    |--------------------------------------------------------------------------
    */
    public function enviarSisab(array $data): void
    {
        $this->enviar(
            config('services.sisab.url'),
            $data
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 🧬 RNDS
    |--------------------------------------------------------------------------
    */
    public function enviarRndS(array $data): void
    {
        $this->enviar(
            config('services.rnds.url'),
            $data
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 💬 WHATSAPP
    |--------------------------------------------------------------------------
    */
    public function enviarWhatsapp(array $data): void
    {
        $this->enviar(
            config('services.whatsapp.url'),
            $data
        );
    }
}
