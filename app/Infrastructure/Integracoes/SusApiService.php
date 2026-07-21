<?php

namespace App\Services\Integracoes;

use Illuminate\Support\Facades\Http;

class SusApiService
{
    public function enviarBPA(string $conteudo)
    {
        if (! config('services.sus.enabled')) {
            return ['status' => false, 'msg' => 'SUS desativado'];
        }

        $response = Http::timeout(config('services.sus.timeout'))
            ->withToken(config('services.sus.token'))
            ->post(config('services.sus.endpoint').'/bpa', [
                'arquivo' => base64_encode($conteudo),
            ]);

        return [
            'status' => $response->successful(),
            'resposta' => $response->json(),
        ];
    }
}
