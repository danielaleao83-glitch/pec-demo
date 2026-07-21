<?php

namespace App\Services\LGPD;

use App\Models\AcessoEmergencial;
use Illuminate\Support\Facades\Auth;

class BreakGlassService
{
    public function registrar(int $pacienteId, string $motivo, $request): AcessoEmergencial
    {
        return AcessoEmergencial::create([
            'user_id' => Auth::id(),
            'paciente_id' => $pacienteId,
            'motivo' => $motivo,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);
    }
}
