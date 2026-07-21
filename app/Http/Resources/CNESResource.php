<?php

declare(strict_types=1);

namespace App\Http\Resources\CNES;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class CNESResource
    extends JsonResource
{
    public function toArray(
        Request $request
    ): array {

        return [

            'uuid'
                => $this['uuid'] ?? null,

            'cnes'
                => $this['cnes'] ?? null,

            'nome_fantasia'
                => $this['nome_fantasia'] ?? null,

            'razao_social'
                => $this['razao_social'] ?? null,

            'municipio'
                => $this['municipio'] ?? null,

            'uf'
                => $this['uf'] ?? null,

            'telefone'
                => $this['telefone'] ?? null,

            'tipo_unidade'
                => $this['tipo_unidade'] ?? null,

            'status'
                => $this['status'] ?? null,
        ];
    }
}