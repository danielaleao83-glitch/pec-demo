<?php

declare(strict_types=1);

namespace App\Services\Cnes\Search;

use Illuminate\Support\Collection;

class CnesSearchEngine
{
    public function __construct(
        private readonly CnesQueryNormalizer $normalizer,
        private readonly CnesRankingService $ranking
    ) {}

    /**
     * 🔎 Busca institucional inteligente
     */
    public function search(string $query): Collection
    {
        $query = $this->normalizer->normalize($query);

        /*
        |--------------------------------------------------------------------------
        | 🚀 FUTURO: ELASTIC QUERY
        |--------------------------------------------------------------------------
        */
        $results = cache()
            ->tags(['cnes_index'])
            ->get('cnes:index:all', collect());

        $filtered = collect($results)->filter(function ($item) use ($query) {
            return str_contains($item['nome'], $query)
                || str_contains($item['municipio'], $query)
                || str_contains((string)$item['cnes'], $query);
        });

        return $this->ranking->rank($filtered, $query);
    }
}