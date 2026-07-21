<<?php

namespace App\Http\Controllers\Atendimento;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Atendimento\AtendimentoService;
use Illuminate\Support\Facades\Log;

class AtendimentoController extends Controller
{
    public function __construct(
        protected AtendimentoService $service
    ) {}

    public function index()
    {
        return response()->json([
            'module' => 'Atendimento',
            'data' => $this->service->list()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'paciente_id' => 'required|integer',
            'profissional_id' => 'required|integer',
            'unidade_id' => 'required|integer',
            'queixa' => 'nullable|string',
            'cid' => 'nullable|string',
            'procedimentos' => 'nullable|array',
        ]);

        $atendimento = $this->service->create($validated);

        Log::channel('daily')->info('ATENDIMENTO_REGISTRADO', [
            'atendimento_id' => $atendimento['id'] ?? null,
            'user_id' => auth()->id(),
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Atendimento registrado com sucesso',
            'data' => $atendimento
        ], 201);
    }

    public function show($id)
    {
        return response()->json([
            'data' => $this->service->find($id)
        ]);
    }

    public function update(Request $request, $id)
    {
        $data = $this->service->update($id, $request->all());

        return response()->json([
            'status' => 'updated',
            'data' => $data
        ]);
    }

    public function destroy($id)
    {
        $this->service->delete($id);

        return response()->json([
            'status' => 'deleted',
            'id' => $id
        ]);
    }
}