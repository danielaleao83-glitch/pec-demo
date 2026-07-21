<?php

declare(strict_types=1);

namespace App\Services\Healthcheck;

use Illuminate\Support\Facades\Http;

class ExternalApisHealthcheck
{
    public function check(): array
    {
        $results = [];

        try {

            // exemplo SISAB
            $response = Http::timeout(2)->get(config('services.sisab.health_url'));

            $results['sisab'] = $response->successful()
                ? 'up'
                : 'down';

        } catch (\Throwable) {
            $results['sisab'] = 'down';
        }

        return [
            'status' => in_array('down', $results, true) ? 'degraded' : 'up',
            'services' => $results,
        ];
    }
}