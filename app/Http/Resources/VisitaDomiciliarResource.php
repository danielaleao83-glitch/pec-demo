<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VisitaDomiciliarResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid, // 🔥 padrão UUID

            'paciente' => [
                'id' => $this->paciente?->uuid,
                'nome' => $this->paciente?->nome,
            ],

            'profissional' => [
                'id' => $this->profissional?->uuid,
                'nome' => $this->profissional?->nome,
            ],

            'data_visita' => optional($this->data_visita)->format('Y-m-d H:i'),

            'tipo' => $this->tipo,
            'observacoes' => $this->observacoes,

            'status' => $this->status,

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            /*
            |------------------------------------------------------
            | 🔐 CONTEXTO (opcional para auditoria/frontend)
            |------------------------------------------------------
            */
            'meta' => [
                'correlation_id' => app('correlation_id') ?? null,
            ],
        ];
    }
}
