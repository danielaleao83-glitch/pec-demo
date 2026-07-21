<?php

namespace App\Services\ESusService;

use App\Models\Atendimento;
use App\Models\Auditoria;
use App\Models\Paciente;
use App\Services\PacienteService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ESusImportService
{
    protected PacienteService $pacienteService;

    public function __construct()
    {
        $this->pacienteService = new PacienteService;
    }

    /**
     * Importa paciente + atendimentos + prescrições do e-SUS AB
     * garantindo auditoria, hash, RLS e validações.
     */
    public function importar(array $dados): Paciente
    {
        // Normaliza dados sensíveis
        $dados['email'] = isset($dados['email']) ? strtolower(trim($dados['email'])) : null;
        $dados['cpf'] = isset($dados['cpf']) ? preg_replace('/\D/', '', $dados['cpf']) : null;
        $dados['cns'] = isset($dados['cns']) ? preg_replace('/\D/', '', $dados['cns']) : null;

        // Validações iniciais
        if (! $this->validarCNS($dados['cns'] ?? '')) {
            throw new \Exception('CNS inválido');
        }
        if (! $this->validarCNES($dados['unidade_cnes'] ?? '')) {
            throw new \Exception('CNES inválido');
        }

        DB::beginTransaction();

        try {
            // ---------------------------
            // Paciente
            // ---------------------------
            $paciente = $this->pacienteService->buscarOuCriar($dados);
            $this->registrarAuditoria($paciente, 'import_eSUS', null, $paciente->toArray());

            // ---------------------------
            // Atendimento
            // ---------------------------
            $atendimento = $paciente->atendimentos()->create([
                'unidade_cnes' => $dados['unidade_cnes'] ?? null,
                'profissional_id' => auth()->id() ?? null,
                'data_atendimento' => $dados['data_atendimento'] ?? now(),
                'cid' => $dados['cid'] ?? null,
            ]);
            $this->registrarAuditoria($atendimento, 'import_eSUS_atendimento', null, $atendimento->toArray());

            // ---------------------------
            // Prescrição (opcional)
            // ---------------------------
            if (! empty($dados['prescricao']) && is_array($dados['prescricao'])) {
                $prescricao = $atendimento->prescricoes()->create($dados['prescricao']);
                $this->registrarAuditoria($prescricao, 'import_eSUS_prescricao', null, $prescricao->toArray());
            }

            DB::commit();

            return $paciente;

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro import e-SUS', ['erro' => $e->getMessage(), 'dados' => $dados]);
            throw $e;
        }
    }

    /**
     * Auditoria governamental
     */
    protected function registrarAuditoria($registro, string $acao, ?array $antes, ?array $depois): void
    {
        Auditoria::create([
            'user_id' => auth()->id() ?? null,
            'acao' => $acao,
            'modulo' => 'eSUSService',
            'registro_id' => $registro->id ?? null,
            'dados_antes' => $antes,
            'dados_depois' => $depois,
            'ip' => request()->ip() ?? 'CLI',
            'user_agent' => request()->userAgent() ?? 'CLI',
            'executado_em' => now(),
            'hash_integridade' => hash('sha256', ($registro->id ?? rand()).json_encode($depois).now()->timestamp),
        ]);
    }

    /**
     * Valida CNS
     */
    public function validarCNS(string $cns): bool
    {
        $cns = preg_replace('/\D/', '', $cns);
        if (empty($cns) || strlen($cns) != 15) {
            return false;
        }
        $soma = 0;
        for ($i = 0; $i < 15; $i++) {
            $soma += $cns[$i] * (15 - $i);
        }

        return ($soma % 11) === 0;
    }

    /**
     * Valida CNES
     */
    public function validarCNES(string $cnes): bool
    {
        return preg_match('/^\d{7}$/', trim($cnes)) === 1;
    }
}
