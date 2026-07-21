<?php

namespace App\Services\Integracoes;

use App\Models\Paciente;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PacienteConsultaService
{
    /**
     * Busca paciente existente ou cria novo com segurança total
     */
    public function buscarOuCriar(array $dados): Paciente
    {
        return DB::transaction(function () use ($dados) {

            $dados = $this->sanitizarDados($dados);

            $cpfHash = $dados['cpf']
                ? hash('sha256', $dados['cpf'])
                : null;

            // 🔐 Busca segura por HASH
            if ($cpfHash) {
                $existente = Paciente::where('cpf_hash', $cpfHash)->first();
                if ($existente) {
                    return $existente;
                }
            }

            // 🔐 Criação segura (somente campos permitidos)
            $paciente = new Paciente;
            $paciente->nome = $dados['nome'] ?? 'NÃO INFORMADO';
            $paciente->cpf = $dados['cpf'] ?? null;
            $paciente->data_nascimento = $dados['data_nascimento'] ?? null;
            $paciente->prioridade = 0;
            $paciente->prioridade_motivo = null;
            $paciente->save();

            $this->definirPrioridadeAutomatica($paciente);

            return $paciente;
        });
    }

    /**
     * Atualiza paciente com proteção contra troca de CPF
     */
    public function atualizar(Paciente $paciente, array $dados): Paciente
    {
        return DB::transaction(function () use ($paciente, $dados) {

            $dados = $this->sanitizarDados($dados);

            if (! empty($dados['cpf'])) {

                $novoHash = hash('sha256', $dados['cpf']);

                // 🔐 Se CPF mudou
                if ($novoHash !== $paciente->cpf_hash) {

                    $existe = Paciente::where('cpf_hash', $novoHash)
                        ->where('id', '!=', $paciente->id)
                        ->exists();

                    if ($existe) {
                        throw new Exception('CPF já cadastrado para outro paciente.');
                    }
                }

                $paciente->cpf = $dados['cpf'];
            }

            if (! empty($dados['nome'])) {
                $paciente->nome = $dados['nome'];
            }

            if (! empty($dados['data_nascimento'])) {
                $paciente->data_nascimento = $dados['data_nascimento'];
            }

            $paciente->save();

            $this->definirPrioridadeAutomatica($paciente);

            return $paciente;
        });
    }

    /**
     * Define prioridade automática conforme idade
     */
    private function definirPrioridadeAutomatica(Paciente $paciente): void
    {
        if (! $paciente->data_nascimento) {
            return;
        }

        $idade = Carbon::parse($paciente->data_nascimento)->age;

        if ($idade >= 60) {
            $paciente->prioridade = 1;
            $paciente->prioridade_motivo = 'Idoso (>= 60 anos)';
            $paciente->save();
        }
    }

    /**
     * Sanitização segura
     */
    private function sanitizarDados(array $dados): array
    {
        return [
            'nome' => isset($dados['nome'])
                ? Str::upper(trim($dados['nome']))
                : null,

            'cpf' => isset($dados['cpf'])
                ? preg_replace('/\D/', '', $dados['cpf'])
                : null,

            'data_nascimento' => $dados['data_nascimento'] ?? null,
        ];
    }
}
