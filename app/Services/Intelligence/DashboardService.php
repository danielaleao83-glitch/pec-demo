<?php

namespace App\Services;

use App\Models\Atendimento;
use App\Models\FilaNotificacao;
use App\Models\Paciente\Paciente;
use App\Models\RegistroMultiprofissional;
use Carbon\Carbon;

class DashboardService
{
    public function getDados()
    {
        return [
            'resumo' => $this->resumo(),
            'graficos' => $this->graficos(),
        ];
    }

    private function resumo()
    {
        return [
            'pacientes_total' => Paciente::count(),

            'atendimentos_hoje' => Atendimento::whereDate('created_at', Carbon::today())->count(),

            'atendimentos_mes' => Atendimento::whereMonth('created_at', Carbon::now()->month)->count(),

            'caps_hoje' => RegistroMultiprofissional::whereDate('created_at', Carbon::today())->count(),

            'mensagens_enviadas' => FilaNotificacao::where('status', 'enviado')->count(),

            'mensagens_erro' => FilaNotificacao::where('status', 'erro')->count(),
        ];
    }

    private function graficos()
    {
        return [
            'atendimentos_por_dia' => $this->atendimentosPorDia(),
            'caps_por_tipo' => $this->capsPorTipo(),
            'mensagens_por_status' => $this->mensagensPorStatus(),
        ];
    }

    private function atendimentosPorDia()
    {
        return Atendimento::selectRaw('DATE(created_at) as data, COUNT(*) as total')
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->groupBy('data')
            ->orderBy('data')
            ->get();
    }

    private function capsPorTipo()
    {
        return RegistroMultiprofissional::selectRaw('tipo_atendimento, COUNT(*) as total')
            ->groupBy('tipo_atendimento')
            ->get();
    }

    private function mensagensPorStatus()
    {
        return FilaNotificacao::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->get();
    }
}
