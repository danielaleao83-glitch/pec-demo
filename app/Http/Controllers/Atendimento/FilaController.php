<?php

namespace App\Http\Controllers\Atendimento;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Atendimento\FilaService;
use Illuminate\Support\Facades\Log;

class FilaController extends Controller
{
    public function __construct(
        protected FilaService $service
    ) {}

    /**
     * 📊 Lista fila por unidade (CNES)
     */
    public function index(Request $request)
    {
        $unidadeId = $request->query('unidade_id');

        return response()->json([
            'module' => 'Atendimento/Fila',
            'data' => $this->service->listByUnit($unidadeId)
        ]);
    }

    /**
     * ➕ Entrada na fila (após triagem)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'paciente_id' => 'required|integer',
            'unidade_id' => 'required|integer',
            'triagem_nivel' => 'required|in:verde,amarelo,laranja,vermelho',
            'queixa' => 'nullable|string',
        ]);

        $fila = $this->service->addToQueue($validated);

        Log::channel('daily')->info('FILA_ENTRADA', [
            'paciente_id' => $validated['paciente_id'],
            'nivel' => $validated['triagem_nivel'],
            'unidade_id' => $validated['unidade_id'],
            'ip' => request()->ip()
        ]);

        return response()->json([
            'status' => 'ok',
            'message' => 'Paciente inserido na fila SUS',
            'data' => $fila
        ], 201);
    }

    /**
     * 👁️ Detalhe da posição na fila
     */
    public function show($id)
    {
        return response()->json([
            'module' => 'Atendimento/Fila',
            'data' => $this->service->find($id)
        ]);
    }

    /**
     * 🔄 Atualiza status da fila (ex: chamado para atendimento)
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:aguardando,em_atendimento,finalizado,ausente'
        ]);

        $data = $this->service->updateStatus($id, $validated['status']);

        return response()->json([
            'status' => 'updated',
            'data' => $data
        ]);
    }

    /**
     * ❌ Remove da fila (somente admin / auditoria)
     */
    public function destroy($id)
    {
        $this->service->remove($id);

        return response()->json([
            'status' => 'removed',
            'id' => $id
        ]);
    }
}