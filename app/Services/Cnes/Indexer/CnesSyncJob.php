<?php

declare(strict_types=1);

namespace App\Services\Cnes\Indexer;

use App\Models\Estabelecimentos\Cnes;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class CnesSyncJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly CnesIndexBuilder $builder
    ) {}

    public function handle(): void
    {
        Cnes::chunk(500, function ($items) {

            foreach ($items as $cnes) {

                $document = $this->builder->build($cnes);

                /*
                |--------------------------------------------------------------------------
                | 🚀 FUTURO: ELASTIC / OPENSEARCH
                |--------------------------------------------------------------------------
                */
                // Elastic::index('cnes', $document);

                /*
                |--------------------------------------------------------------------------
                | fallback atual (cache/index interno)
                |--------------------------------------------------------------------------
                */
                cache()->put(
                    "cnes:index:{$cnes->id}",
                    $document,
                    now()->addDay()
                );
            }
        });
    }
}