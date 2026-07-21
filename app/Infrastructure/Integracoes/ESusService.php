<?php

namespace App\Services\Integracoes;

use App\Models\Atendimento\Atendimento;
use App\Models\Auditoria\Auditoria;
use App\Models\Clinico\Prescricao;
use App\Models\Paciente\Paciente;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ESusService
{
    /**
     * 🔐 Importação completa estilo e-SUS
     */
    public function importar(array $dados): Paciente
    {
        $dados = $this->normalizarDados($dados);

        // 🔒 Validações críticas
        if (! $this->validarCNS($dados['cns'] ?? '')) {
            throw new \InvalidArgumentException('CNS inválido');
        }

        if (! $this->validarCNES($dados['unidade_cnes'] ?? '')) {
            throw new \InvalidArgumentException('CNES inválido');
        }

        DB::beginTransaction();

        try {

            // 👤 PACIENTE
            $paciente = $this->buscarOuCriarPaciente($dados);

            // 🏥 ATENDIMENTO
            $atendimento = $this->criarAtendimento($paciente, $dados);

            // 💊 PRESCRIÇÃO (opcional)
            if (! empty($dados['prescricao'])) {
                $this->criarPrescricao($atendimento, $dados['prescricao']);
            }

            DB::commit();

            return $paciente;

        } catch (Throwable $e) {

            DB::rollBack();

            Log::error('Erro ESusService', [
                'erro' => $e->getMessage(),
                'dados' => $dados,
            ]);

            throw $e;
        }
    }

    /**
     * 👤 Buscar ou criar paciente
     */
    protected function buscarOuCriarPaciente(array $dados): Paciente
    {
        $paciente = Paciente::query()
            ->when($dados['cpf'] ?? null, fn ($q) => $q->porCpf($dados['cpf']))
            ->orWhere(fn ($q) => $q->when($dados['cns'] ?? null, fn ($q2) => $q2->porCns($dados['cns']))
            )
            ->first();

        if (! $paciente) {
            $paciente = new Paciente;
            $paciente->fill($dados);
            $paciente->save();

            $this->registrarAuditoria($paciente, 'create');
        } else {
            $antes = $paciente->getOriginal();

            $paciente->fill($dados);
            $paciente->save();

            $this->registrarAuditoria($paciente, 'update', $antes);
        }

        return $paciente;
    }

    /**
     * 🏥 Criar atendimento
     */
    protected function criarAtendimento(Paciente $paciente, array $dados): Atendimento
    {
        $atendimento = new Atendimento;

        $atendimento->paciente_id = $paciente->id;
        $atendimento->unidade_cnes = $dados['unidade_cnes'] ?? null;
        $atendimento->profissional_id = Auth::id();
        $atendimento->data_atendimento = $dados['data_atendimento'] ?? now();
        $atendimento->cid = $dados['cid'] ?? null;

        $atendimento->save();

        $this->registrarAuditoria($atendimento, 'create');

        return $atendimento;
    }

    /**
     * 💊 Criar prescrição
     */
    protected function criarPrescricao(Atendimento $atendimento, array $dados): Prescricao
    {
        $prescricao = new Prescricao;

        $prescricao->atendimento_id = $atendimento->id;
        $prescricao->fill($dados);

        // 🔐 Sanitização forte
        $prescricao->sigtap = strtoupper(trim(strip_tags($dados['sigtap'] ?? '')));

        $prescricao->save();

        $this->registrarAuditoria($prescricao, 'create');

        return $prescricao;
    }

    /**
     * 🔒 Normalização de dados
     */
    protected function normalizarDados(array $dados): array
    {
        return [
            ...$dados,
            'cpf' => isset($dados['cpf']) ? preg_replace('/\D/', '', $dados['cpf']) : null,
            'cns' => isset($dados['cns']) ? preg_replace('/\D/', '', $dados['cns']) : null,
            'email' => isset($dados['email']) ? strtolower(trim($dados['email'])) : null,
            'nome' => isset($dados['nome']) ? trim(strip_tags($dados['nome'])) : null,
        ];
    }

    /**
     * 🧾 Auditoria padrão SUS
     */
    protected function registrarAuditoria($model, string $acao, ?array $antes = null): void
    {
        try {

            Auditoria::create([
                'user_id' => Auth::id(),
                'acao' => $acao,
                'modulo' => class_basename($model),
                'registro_id' => $model->id,
                'dados_antes' => $antes ?? $model->getOriginal(),
                'dados_depois' => $model->getAttributes(),
                'ip' => request()->ip() ?? 'CLI',
                'user_agent' => request()->userAgent() ?? 'CLI',
                'executado_em' => now(),
                'hash_integridade' => hash(
                    'sha256',
                    json_encode($model->getAttributes()).now()->timestamp.Str::uuid()
                ),
            ]);

        } catch (Throwable $e) {
            Log::error('Erro auditoria ESus', [
                'erro' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 🧠 Validação CNS
     */
    public function validarCNS(string $cns): bool
    {
        $cns = preg_replace('/\D/', '', $cns);

        if (strlen($cns) !== 15) {
            return false;
        }

        $soma = 0;

        for ($i = 0; $i < 15; $i++) {
            $soma += $cns[$i] * (15 - $i);
        }

        return ($soma % 11) === 0;
    }

    /**
     * 🏥 Validação CNES
     */
    public function validarCNES(string $cnes): bool
    {
        return preg_match('/^\d{7}$/', trim($cnes)) === 1;
    }

    /**
     * 🧾 Validação CPF completa
     */
    public function validarCPF(string $cpf): bool
    {
        $cpf = preg_replace('/\D/', '', $cpf);

        if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $d = 0;

            for ($c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }

            $d = ((10 * $d) % 11) % 10;

            if ($cpf[$c] != $d) {
                return false;
            }
        }

        return true;
    }

    /**
     * 🧪 Exportação estilo SISAB (mock)
     */
    public function exportarSISAB(Atendimento $atendimento): array
    {
        return [
            'unidade_cnes' => $atendimento->unidade_cnes,
            'data_atendimento' => optional($atendimento->data_atendimento)->format('Y-m-d'),

            'paciente' => [
                'nome' => $atendimento->paciente->nome,
                'cns' => $atendimento->paciente->cns,
                'cpf' => $atendimento->paciente->cpf,
                'sexo' => $atendimento->paciente->sexo,
                'data_nascimento' => $atendimento->paciente->data_nascimento,
            ],

            'profissional' => [
                'id' => $atendimento->profissional_id,
            ],

            'cid' => $atendimento->cid,

            'procedimentos' => $atendimento->procedimentos ?? [],

            'hash_integridade' => hash(
                'sha256',
                json_encode($atendimento->toArray())
            ),
        ];
    }
}
