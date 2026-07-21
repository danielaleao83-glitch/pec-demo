<?php

namespace App\Services\ESusService\SUS;

use App\Models\SusIntegracao;
use Illuminate\Support\Collection;

class SusLogService
{
    /*
    |--------------------------------------------------------------------------
    | 📅 CONSULTA POR PERÍODO (AUDITORIA FEDERAL)
    |--------------------------------------------------------------------------
    */
    public function listarPorPeriodo($inicio, $fim): Collection
    {
        return SusIntegracao::query()
            ->whereBetween('created_at', [$inicio, $fim])
            ->orderByDesc('created_at')
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | 👤 CONSULTA POR PACIENTE (LGPD SAFE + JSON SAFE)
    |--------------------------------------------------------------------------
    */
    public function listarPorPaciente($pacienteId): Collection
    {
        if (empty($pacienteId)) {
            return collect();
        }

        return SusIntegracao::query()
            ->where('payload->paciente->id', $pacienteId)
            ->orderByDesc('created_at')
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | 📡 CONSULTA POR STATUS HTTP (RNDS STYLE)
    |--------------------------------------------------------------------------
    */
    public function listarPorStatus($status): Collection
    {
        return SusIntegracao::query()
            ->where('status_code', $status)
            ->orderByDesc('created_at')
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | 🧠 ESTATÍSTICA FEDERAL (MONITORAMENTO SUS)
    |--------------------------------------------------------------------------
    */
    public function estatisticas($inicio = null, $fim = null): array
    {
        $query = SusIntegracao::query();

        if ($inicio && $fim) {
            $query->whereBetween('created_at', [$inicio, $fim]);
        }

        return [
            'total' => (clone $query)->count(),
            'sucesso' => (clone $query)->where('status_code', 200)->count(),
            'erro' => (clone $query)->where('status_code', '>=', 400)->count(),
        ];
    }
}