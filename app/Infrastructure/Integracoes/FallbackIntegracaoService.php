<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FallbackIntegracaoService
{
    /*
    |--------------------------------------------------------------------------
    | 💾 SALVA OFFLINE (CASO SISAB CAIA)
    |--------------------------------------------------------------------------
    */
    public function armazenarOffline(string $tipo, array $payload): void
    {
        $key = "fallback:{$tipo}:".uniqid();

        Cache::put($key, $payload, now()->addDays(7));

        Log::warning('FALLBACK ARMAZENADO', [
            'tipo' => $tipo,
            'payload' => $payload,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 🔄 RECUPERAR OFFLINE
    |--------------------------------------------------------------------------
    */
    public function recuperarPendentes(string $tipo): array
    {
        $items = [];

        foreach (Cache::getStore()->getPrefix() as $key) {
            if (str_contains($key, "fallback:{$tipo}")) {
                $items[] = Cache::get($key);
            }
        }

        return $items;
    }
}
