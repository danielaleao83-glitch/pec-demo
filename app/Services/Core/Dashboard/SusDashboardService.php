<?php

namespace App\Services\Core\Dashboard;

use App\Models\Atendimento;
use App\Models\VisitaDomiciliar;
use App\Models\Paciente;

class SusDashboardService
{
    /**
     * 📊 Indicadores gerais SUS (produção + gestão)
     */
    public function indicadores(): array
    {
        return [
            'pacientes_total' => Paciente::count(),

            'atendimentos_hoje' => Atendimento::whereDate('created_at', today())->count(),

            'visitas_pendentes' => VisitaDomiciliar::where('pendente_envio', true)->count(),

            'visitas_enviadas' => VisitaDomiciliar::where('enviado_sisab', true)->count(),

            'taxa_atendimento' => $this->taxaAtendimento(),
        ];
    }

    /**
     * 📈 Taxa simples de cobertura operacional
     */
    protected function taxaAtendimento(): float
    {
        $total = Atendimento::count();

        if ($total === 0) {
            return 0.0;
        }

        $hoje = Atendimento::whereDate('created_at', today())->count();

        return round(($hoje / $total) * 100, 2);
    }

    /**
     * 🧠 resumo operacional SUS
     */
    public function resumoOperacional(): array
    {
        return [
            'status_sistema' => 'OPERACIONAL',
            'carga_diaria' => Atendimento::whereDate('created_at', today())->count(),
            'alerta_fila' => VisitaDomiciliar::where('pendente_envio', true)->count() > 100,
        ];
    }
}