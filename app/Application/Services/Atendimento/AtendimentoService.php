<<?php

namespace App\Services\Atendimento;

use App\Models\Atendimento;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AtendimentoService
{
    /**
     * 📋 LISTAGEM SEGURA (fila ou produção)
     */
    public function list(array $filters = [])
    {
        return Atendimento::query()
            ->when(isset($filters['status']), function ($q) use ($filters) {
                $q->where('status', $filters['status']);
            })
            ->when(isset($filters['paciente_id']), function ($q) use ($filters) {
                $q->where('paciente_id', $filters['paciente_id']);
            })
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * 🏥 CRIAÇÃO CLÍNICA (com rastreabilidade SUS)
     */
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {

            $atendimento = Atendimento::create([
                'paciente_id' => $data['paciente_id'],
                'profissional_id' => $data['profissional_id'] ?? null,
                'unidade_id' => $data['unidade_id'] ?? null,

                'status' => 'registrado',
                'prioridade' => $data['prioridade'] ?? 0,

                'queixa' => $data['queixa'] ?? null,
                'cid' => $data['cid'] ?? null,

                'status_anterior' => null,
                'ultimo_evento' => 'ATENDIMENTO_REGISTRADO',
            ]);

            Log::channel('security')->info('ATENDIMENTO_CRIADO', [
                'atendimento_id' => $atendimento->id,
                'paciente_id' => $atendimento->paciente_id,
                'status' => $atendimento->status,
                'timestamp' => now()->toIso8601String(),
                'ip' => request()->ip(),
            ]);

            return $atendimento;
        });
    }

    /**
     * 🔍 BUSCA SEGURA
     */
    public function find($id)
    {
        return Atendimento::with(['paciente'])
            ->findOrFail($id);
    }

    /**
     * 🔄 ATUALIZAÇÃO COM CONTROLE DE ESTADO CLÍNICO
     */
    public function update($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {

            $atendimento = Atendimento::findOrFail($id);

            $statusAnterior = $atendimento->status;

            $atendimento->update([
                'status' => $data['status'] ?? $atendimento->status,
                'prioridade' => $data['prioridade'] ?? $atendimento->prioridade,
                'queixa' => $data['queixa'] ?? $atendimento->queixa,
                'cid' => $data['cid'] ?? $atendimento->cid,

                'status_anterior' => $statusAnterior,
                'ultimo_evento' => 'ATENDIMENTO_ATUALIZADO',
                'ultimo_evento_em' => now(),
            ]);

            Log::channel('security')->info('ATENDIMENTO_ATUALIZADO', [
                'atendimento_id' => $atendimento->id,
                'status_anterior' => $statusAnterior,
                'status_atual' => $atendimento->status,
                'timestamp' => now()->toIso8601String(),
            ]);

            return $atendimento;
        });
    }

    /**
     * 🗑️ DELETE CONTROLADO (SUS NÃO DELETA REAL — SÓ INATIVA)
     */
    public function delete($id)
    {
        return DB::transaction(function () use ($id) {

            $atendimento = Atendimento::findOrFail($id);

            $atendimento->update([
                'status' => 'cancelado',
                'status_anterior' => $atendimento->status,
                'ultimo_evento' => 'ATENDIMENTO_CANCELADO',
                'ultimo_evento_em' => now(),
            ]);

            Log::channel('security')->warning('ATENDIMENTO_CANCELADO', [
                'atendimento_id' => $atendimento->id,
                'timestamp' => now()->toIso8601String(),
                'ip' => request()->ip(),
            ]);

            return true;
        });
    }
}