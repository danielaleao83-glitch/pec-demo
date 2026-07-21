<?php

namespace App\Services;

use App\Models\VisitaDomiciliar;

class TerritorialIntelligenceService
{
    public function calcularRiscoFamilia($familiaId): string
    {
        $visitas = VisitaDomiciliar::where('familia_id', $familiaId)->get();

        $score = 0;

        foreach ($visitas as $v) {

            if ($v->condicoes_higiene >= 2) {
                $score += 2;
            }
            if ($v->situacao_moradia == 3) {
                $score += 2;
            }

            if ($v->imovel_visitado === false) {
                $score += 3;
            }

            if ($v->observacoes && str_contains(strtolower($v->observacoes), 'risco')) {
                $score += 3;
            }

            if ($v->sinais_vitais) {
                $score += 1;
            }
        }

        return match (true) {
            $score >= 10 => 'CRITICO',
            $score >= 6 => 'ALTO',
            $score >= 3 => 'MEDIO',
            default => 'BAIXO',
        };
    }

    public function indicadoresMicroarea(string $microarea)
    {
        $visitas = VisitaDomiciliar::where('microarea', $microarea)->get();

        return [
            'total_visitas' => $visitas->count(),
            'familias_atendidas' => $visitas->pluck('familia_id')->unique()->count(),
            'pacientes_visitados' => $visitas->pluck('paciente_id')->unique()->count(),
            'risco_alto' => $visitas->where('condicoes_higiene', '>=', 2)->count(),
            'nao_visitados' => $visitas->where('imovel_visitado', false)->count(),
        ];
    }
}
