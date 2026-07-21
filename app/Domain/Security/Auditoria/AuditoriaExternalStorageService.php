<?php

namespace App\Services\Security\Auditoria;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuditoriaExternalStorageService
{
    public static function enviar(array $dados): void
    {
        try {
            $datePath = now()->format('Y/m/d');

            $file = 'auditoria/'
                . $datePath . '/'
                . Str::uuid() . '.json';

            $payload = json_encode(
                $dados,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );

            Storage::disk('s3')->put($file, $payload);

        } catch (\Throwable $e) {

            Log::error('Falha envio auditoria externa', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'timestamp' => now()->toISOString(),
                'service' => 'AuditoriaExternalStorageService',
            ]);
        }
    }
}