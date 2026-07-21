<?php

declare(strict_types=1);

namespace App\Services\Cnes\Search;

use Illuminate\Support\Collection;

class CnesRankingService
{
    public function rank(Collection $results, string $query): Collection
    {
        return $results->sortByDesc(function ($item) use ($query) {

            $score = 0;

            if (str_starts_with($item['cnes'], $query)) {
                $score += 100;
            }

            if (str_contains($item['nome'], $query)) {
                $score += 80;
            }

            if (str_contains($item['municipio'], $query)) {
                $score += 60;
            }

            return $score;
        })->values();
    }
}