<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Models\Atendimento;

class MetricsService
{
    public function atendimentosHoje(string $unidadeId): int
    {
        return Atendimento::whereDate('created_at', today())
            ->where('unidade_id', $unidadeId)
            ->count();
    }

    public function triagensHoje(string $unidadeId): int
    {
        return Atendimento::where('status', 'triagem')
            ->where('unidade_id', $unidadeId)
            ->count();
    }

    public function encaminhamentosHoje(string $unidadeId): int
    {
        return Atendimento::where('status', 'encaminhado')
            ->where('unidade_id', $unidadeId)
            ->count();
    }

    public function emAtendimento(string $unidadeId): int
    {
        return Atendimento::where('status', 'em_atendimento')
            ->where('unidade_id', $unidadeId)
            ->count();
    }
}