<?php

declare(strict_types=1);

namespace App\Services\APS;

use App\Models\Familia;
use Illuminate\Support\Facades\DB;

final class FamiliaService
{
    public function list(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Familia::query()
            ->select([
                'id',
                'nome_responsavel',
                'microarea',
                'created_at'
            ])
            ->latest()
            ->paginate(20);
    }

    public function create(array $data): Familia
    {
        return DB::transaction(function () use ($data) {

            $familia = Familia::create([
                'nome_responsavel' => $this->sanitizeName($data['nome_responsavel']),
                'cpf_hash' => $this->hashCpf($data['cpf_responsavel'] ?? null),
                'microarea' => $data['microarea'] ?? null,

                'registrado_por' => auth()->id(),
                'registrado_em' => now(),
            ]);

            $familia->update([
                'hash_integridade' => $this->generateIntegrityHash($familia),
            ]);

            return $familia;
        });
    }

    /**
     * 🧼 Sanitização mínima de identidade
     */
    private function sanitizeName(string $name): string
    {
        return trim(mb_strtoupper($name));
    }

    /**
     * 🔐 HASH DE CPF (sem correlação direta)
     */
    private function hashCpf(?string $cpf): ?string
    {
        if (!$cpf) {
            return null;
        }

        $normalized = preg_replace('/\D/', '', $cpf);

        return hash('sha256', config('app.key') . '|' . $normalized);
    }

    /**
     * 🔐 HASH DE INTEGRIDADE (estável e auditável)
     */
    private function generateIntegrityHash(Familia $familia): string
    {
        return hash('sha256', implode('|', [
            $familia->id,
            $familia->nome_responsavel,
            $familia->cpf_hash ?? '',
            $familia->microarea ?? '',
            $familia->registrado_por,
            $familia->registrado_em,
        ]));
    }
}