<?php

namespace App\Services\Atendimento;

use App\Models\Atendimento;

class QueueService
{
    public function waiting($unidadeId): int
    {
        return Atendimento::where('status', 'aguardando')
            ->where('unidade_id', $unidadeId)
            ->count();
    }

    public function called($unidadeId): int
    {
        return Atendimento::where('status', 'chamado')
            ->where('unidade_id', $unidadeId)
            ->count();
    }

    public function inProgress($unidadeId): int
    {
        return Atendimento::where('status', 'em_atendimento')
            ->where('unidade_id', $unidadeId)
            ->count();
    }
}