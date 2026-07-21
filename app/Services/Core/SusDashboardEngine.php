<?php

namespace App\Services\Core\Dashboard;

use App\Models\Atendimento;
use App\Models\VisitaDomiciliar;
use App\Models\Paciente;

class SusDashboardEngine
{
    /**
     * 📊 PAINEL CENTRAL SUS
     */
    public function metrics(): array
    {
        return [
            'pacientes' => Paciente::count(),

            'atendimentos_hoje' => Atendimento::whereDate('created_at', today())->count(),

            'visitas_pendentes' => VisitaDomiciliar::where('pendente_envio', true)->count(),

            'visitas_enviadas' => VisitaDomiciliar::where('enviado_sisab', true)->count(),

            'status_sistema' => 'OPERACIONAL',

            'alerta_fila' => $this->filaCritica(),
        ];
    }

    /**
     * 🚨 alerta crítico SUS
     */
    protected function filaCritica(): bool
    {
        return VisitaDomiciliar::where('pendente_envio', true)->count() > 200;
    }
}