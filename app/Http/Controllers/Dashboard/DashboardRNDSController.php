<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Dashboard\RndsRealtimeDashboardService;

class DashboardRNDSController extends Controller
{
    public function __construct(
        private RndsRealtimeDashboardService $service
    ) {}

    /**
     * 📡 STREAM CLÍNICO RNDS
     */
    public function realtime(Request $request)
    {
        return response()->json([
            'stream' => true,

            'payload' => $this->service->snapshot(
                user: $request->user(),
                unidadeId: $request->user()?->unidade_id
            ),

            'mode' => 'WEBSOCKET_READY',
        ]);
    }
}