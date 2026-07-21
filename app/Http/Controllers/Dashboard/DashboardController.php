<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\Dashboard\RndsRealtimeDashboardService;

class DashboardController extends Controller
{
    public function __construct(
        private RndsRealtimeDashboardService $service
    ) {}

    /**
     * 🏥 DASHBOARD FEDERAL RNDS (SNAPSHOT)
     */
    public function index(Request $request)
    {
        return response()->json([
            'system' => 'eSUS_APS_RNDS',
            'version' => '10.0-FEDERAL',
            'request_id' => (string) Str::uuid(),

            'data' => $this->service->snapshot(
                user: $request->user(),
                unidadeId: $request->user()?->unidade_id
            ),

            'timestamp' => now()->toIso8601String(),
        ]);
    }
}