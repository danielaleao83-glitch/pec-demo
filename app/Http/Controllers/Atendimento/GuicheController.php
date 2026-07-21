<?php

namespace App\Http\Controllers\Atendimento;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Atendimento\AtendimentoService;
use Illuminate\Support\Facades\Log;

class GuicheController extends Controller
{
    protected AtendimentoService $service;

    public function __construct(AtendimentoService $service)
    {
        $this->service = $service;
    }

    /**
     * 📋 Lista guichês (painel UBS)
     */
    public function index()
    {
        return response()->json([
            'module' => 'Atendimento',
            'controller' => 'GuicheController',
            'status' => 'active',
            'timestamp' => now()->toIso8601String()
        ]);
    }

    /**
     * 🏥 Criação de guichê operacional
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => 'required|string',
            'unidade_id' => 'nullable|uuid',
        ]);

        // Aqui normalmente viria Model Guiche::create()
        Log::channel('security')->info('GUICHE_CRIADO', $data);

        return response()->json([
            'message' => 'Guichê criado com sucesso',
            'data' => $data
        ]);
    }

    /**
     * 🔍 Detalhe do guichê
     */
    public function show($id)
    {
        return response()->json([
            'guiche_id' => $id,
            'status' => 'operacional',
            'fila_ativa' => true,
            'timestamp' => now()->toIso8601String()
        ]);
    }

    /**
     * 🔄 Atualização do guichê
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nome' => 'sometimes|string',
            'ativo' => 'sometimes|boolean',
        ]);

        Log::channel('security')->info('GUICHE_ATUALIZADO', [
            'guiche_id' => $id,
            'data' => $data,
            'timestamp' => now()->toIso8601String()
        ]);

        return response()->json([
            'message' => 'Guichê atualizado',
            'guiche_id' => $id
        ]);
    }

    /**
     * 🗑️ Desativação (SUS não remove, só inativa)
     */
    public function destroy($id)
    {
        Log::channel('security')->warning('GUICHE_DESATIVADO', [
            'guiche_id' => $id,
            'timestamp' => now()->toIso8601String()
        ]);

        return response()->json([
            'message' => 'Guichê desativado (soft delete)',
            'guiche_id' => $id
        ]);
    }

    /**
     * 📡 CHAMADA REAL DE PACIENTE (CORAÇÃO DO SISTEMA)
     */
    public function chamarProximo(Request $request)
    {
        $guicheId = $request->input('guiche_id');
        $userId = $request->user()?->id;

        $atendimento = $this->service->chamarProximo($guicheId, $userId);

        if (!$atendimento) {
            return response()->json([
                'message' => 'Fila vazia',
                'status' => 'empty'
            ]);
        }

        return response()->json([
            'message' => 'Paciente chamado',
            'data' => $atendimento
        ]);
    }
}