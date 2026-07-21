<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\EsusIndicatorService;
use Illuminate\Http\Request;

class DashboardSUSController extends Controller
{
    public function __construct(
        private EsusIndicatorService $service
    ) {}

    /**
     * 📊 INDICADORES e-SUS APS
     */
    public function index(Request $request)
    {
        return response()->json([
            'module' => 'eSUS_APS_INDICATORS',

            'data' => $this->service->generate(
                unidadeId: $request->user()?->unidade_id
            ),

            'generated_at' => now(),
        ]);
    }
}