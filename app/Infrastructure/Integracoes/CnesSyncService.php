<?php

namespace App\Services\SISAB;

use Illuminate\Support\Facades\Http;

class CnesSyncService
{
    public function syncUnidade($cnes)
    {
        return Http::get("https://cnes.datasus.gov.br/api/unidade", [
            'cnes' => $cnes
        ])->json();
    }
}