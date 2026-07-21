<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\RelatorioService;
use Illuminate\Http\Request;

class RelatorioController extends Controller
{
    public function __construct(
        private RelatorioService $service
    ) {}

    /**
     * 📊 RELATÓRIO FEDERAL e-SUS
     */
    public function index(Request $request)
    {
        return response()->json([
            'report' => $this->service->generate(
                unidadeId: $request->user()?->unidade_id
            ),

            'format' => 'FEDERAL_AGGREGATED',
        ]);
    }
}