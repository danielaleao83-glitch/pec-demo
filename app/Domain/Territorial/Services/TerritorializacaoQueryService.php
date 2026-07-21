<?php

declare(strict_types=1);

namespace App\Services\Territorial;

use App\Models\Territorializacao;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class TerritorializacaoQueryService
{
    private const DEFAULT_PER_PAGE = 20;

    private const MAX_PER_PAGE = 100;

    /**
     * 🗺️ PAGINAÇÃO TERRITORIAL
     */
    public function paginate(
        array $filters
    ): LengthAwarePaginator {

        $perPage = min(
            (int) ($filters['per_page'] ?? self::DEFAULT_PER_PAGE),
            self::MAX_PER_PAGE
        );

        return $this->query($filters)
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * 🔎 QUERY BASE
     */
    private function query(array $filters): Builder
    {
        $query = Territorializacao::query()
            ->select([
                'id',
                'municipio_id',
                'equipe_id',
                'microarea',
                'descricao',
                'geo_json',
                'created_at',
                'updated_at',
            ]);

        /*
        |--------------------------------------------------------------------------
        | 🧭 FILTROS
        |--------------------------------------------------------------------------
        */

        $query->when(
            $filters['municipio_id'] ?? null,
            fn (Builder $q, int $municipioId) => $q->where(
                'municipio_id',
                $municipioId
            )
        );

        $query->when(
            $filters['equipe_id'] ?? null,
            fn (Builder $q, int $equipeId) => $q->where(
                'equipe_id',
                $equipeId
            )
        );

        $query->when(
            $filters['microarea'] ?? null,
            fn (Builder $q, string $microarea) => $q->where(
                'microarea',
                'ILIKE',
                '%' . trim($microarea) . '%'
            )
        );

        return $query->orderByDesc('created_at');
    }
}