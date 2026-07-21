<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\QueueRealtimeService;
use Illuminate\Http\Request;

class PainelController extends Controller
{
    public function __construct(
        private QueueRealtimeService $queue
    ) {}

    /**
     * 🏥 PAINEL OPERACIONAL (GUICHÊ)
     */
    public function index(Request $request)
    {
        $unidadeId = $request->user()?->unidade_id;

        return response()->json([
            'fila' => [
                'aguardando' => $this->queue->waiting($unidadeId),
                'chamados' => $this->queue->called($unidadeId),
                'em_atendimento' => $this->queue->inProgress($unidadeId),
            ],

            'guiche' => [
                'ativo' => true,
                'user' => $request->user()?->id,
            ],
        ]);
    }
}