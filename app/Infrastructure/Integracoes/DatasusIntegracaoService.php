<?php

namespace App\Services\Integracoes;

use Illuminate\Support\Facades\Http;

class DatasusIntegracaoService
{
    public function consultarDadosPublicos($endpoint)
    {
        return Http::timeout(10)->get($endpoint)->json();
    }
}
