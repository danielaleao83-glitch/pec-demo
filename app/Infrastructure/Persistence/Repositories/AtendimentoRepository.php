<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Atendimento\Entities\Atendimento;
use App\Domain\Atendimento\Repositories\AtendimentoRepositoryInterface;
use App\Models\Atendimento as AtendimentoModel;

class AtendimentoRepository implements AtendimentoRepositoryInterface
{
    public function buscarProximo(): ?Atendimento
    {
        $model = AtendimentoModel::query()
            ->where('status', 'aguardando')
            ->orderByDesc('prioridade')
            ->orderBy('created_at')
            ->lockForUpdate()
            ->first();

        if (!$model) {
            return null;
        }

        return new Atendimento(
            new \App\Domain\Atendimento\Entities\AtendimentoId($model->id),
            $model->paciente_id,
            (int) $model->prioridade,
            new \DateTimeImmutable($model->created_at)
        );
    }

    public function salvar(Atendimento $atendimento): void
    {
        AtendimentoModel::where('id', $atendimento->id()->value())
            ->update([
                'status' => $atendimento->status(),
                'updated_at' => now()
            ]);
    }
}