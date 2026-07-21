<?php

namespace App\Services\Paciente;

use App\Models\PacienteAuditoria;
use Illuminate\Support\Facades\Auth;

class PacienteAuditoriaService
{
    public function registrar(string $acao, int $pacienteId, $request): void
    {
        PacienteAuditoria::create([
            'user_id' => Auth::id(),
            'acao' => $acao,
            'detalhes' => json_encode([
                'paciente_id' => $pacienteId,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now(),
            ]),
        ]);
    }
}
