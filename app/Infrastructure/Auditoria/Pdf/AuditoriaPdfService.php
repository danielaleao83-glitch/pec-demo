<?php

namespace App\Infrastructure\Auditoria\Pdf;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AuditoriaPdfService
{
    public function gerar(): BinaryFileResponse
    {
        $dados = DB::table('auditorias')
            ->orderBy('executado_em')
            ->get();

        $pdf = Pdf::loadView('auditoria.pdf', [
            'registros' => $dados,
        ]);

        return $pdf->download('auditoria_forense.pdf');
    }
}