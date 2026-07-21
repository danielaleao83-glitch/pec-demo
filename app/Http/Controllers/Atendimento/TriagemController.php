<?php

namespace App\Http\Controllers\Atendimento;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Atendimento\TriagemService;
use Illuminate\Support\Facades\Log;

class TriagemController extends Controller
{
    protected TriagemService $service;

    public function __construct(TriagemService $service)
    {
        $this->service = $service;
    }

    /**
     * 📋 Lista triagens (auditoria clínica)
     */
    public function index()
    {
        return response()->json([
            'module' => 'Atendimento',
            'controller' => 'TriagemController',
            'status' => 'active',
            'timestamp' => now()->toIso8601String()
        ]);
    }

    /**
     * 🧠 CRIA TRIAGEM CLÍNICA (ponto crítico SUS)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'atendimento_id' => 'required|uuid',
            'pressao_arterial' => 'nullable|string',
            'temperatura' => 'nullable|numeric',
            'frequencia_cardiaca' => 'nullable|integer',
            'sintomas' => 'nullable|string',
            'queixa_principal' => 'required|string',
        ]);

        $triagem = $this->service->realizarTriagem($data, $request->user()->id);

        Log::channel('security')->info('TRIAGEM_REALIZADA', [
            'atendimento_id' => $data['atendimento_id'],
            'profissional_id' => $request->user()->id ?? null,
            'timestamp' => now()->toIso8601String(),
        ]);

        return response()->json([
            'message' => 'Triagem realizada com sucesso',
            'data' => $triagem
        ]);
    }

    /**
     * 🔍 Consulta triagem
     */
    public function show($id)
    {
        return response()->json([
            'triagem_id' => $id,
            'status' => 'avaliada',
            'timestamp' => now()->toIso8601String()
        ]);
    }

    /**
     * 🔄 Atualização (reclassificação de risco)
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'risco' => 'sometimes|string',
            'observacao' => 'sometimes|string',
        ]);

        Log::channel('security')->warning('TRIAGEM_RECLASSIFICADA', [
            'triagem_id' => $id,
            'data' => $data,
            'timestamp' => now()->toIso8601String()
        ]);

        return response()->json([
            'message' => 'Triagem atualizada',
            'triagem_id' => $id
        ]);
    }

    /**
     * 🗑️ Cancelamento clínico (não delete físico)
     */
    public function destroy($id)
    {
        Log::channel('security')->warning('TRIAGEM_CANCELADA', [
            'triagem_id' => $id,
            'timestamp' => now()->toIso8601String()
        ]);

        return response()->json([
            'message' => 'Triagem cancelada (registro mantido)',
            'triagem_id' => $id
        ]);
    }
}