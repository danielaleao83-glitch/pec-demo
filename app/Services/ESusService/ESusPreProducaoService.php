<?php

namespace App\Services\ESusService;

use App\Models\Atendimento;
use App\Models\Auditoria;
use App\Models\Paciente;
use App\Services\PacienteService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ESusPreProducaoService
{
    protected PacienteService $pacienteService;

    public function __construct()
    {
        $this->pacienteService = new PacienteService;
    }

    /**
     * Importa paciente + atendimentos + prescrições e envia para e-SUS AB / RNDS
     * Nível Governamental, logs forenses, auditoria e RLS
     */
    public function importar(array $dados): Paciente
    {
        // Normaliza dados sensíveis
        $dados['email'] = isset($dados['email']) ? strtolower(trim($dados['email'])) : null;
        $dados['cpf'] = isset($dados['cpf']) ? preg_replace('/\D/', '', $dados['cpf']) : null;
        $dados['cns'] = isset($dados['cns']) ? preg_replace('/\D/', '', $dados['cns']) : null;

        // Validações críticas
        if (! $this->validarCNS($dados['cns'] ?? '')) {
            Log::warning('CNS inválido', $dados);
            throw new \Exception('CNS inválido');
        }
        if (! $this->validarCNES($dados['unidade_cnes'] ?? '')) {
            Log::warning('CNES inválido', $dados);
            throw new \Exception('CNES inválido');
        }

        DB::beginTransaction();

        try {
            // ---------------------------
            // Paciente
            // ---------------------------
            $paciente = $this->pacienteService->buscarOuCriar($dados);
            $this->registrarAuditoria($paciente, 'import_eSUS_preprod', null, $paciente->toArray());

            // ---------------------------
            // Atendimento
            // ---------------------------
            $atendimento = $paciente->atendimentos()->create([
                'unidade_cnes' => $dados['unidade_cnes'] ?? null,
                'profissional_id' => auth()->id() ?? null,
                'data_atendimento' => $dados['data_atendimento'] ?? now(),
                'cid' => $dados['cid'] ?? null,
            ]);
            $this->registrarAuditoria($atendimento, 'import_eSUS_preprod_atendimento', null, $atendimento->toArray());

            // ---------------------------
            // Prescrição (opcional)
            // ---------------------------
            if (! empty($dados['prescricao']) && is_array($dados['prescricao'])) {
                $prescricao = $atendimento->prescricoes()->create($dados['prescricao']);
                $this->registrarAuditoria($prescricao, 'import_eSUS_preprod_prescricao', null, $prescricao->toArray());
            }

            DB::commit();

            // ---------------------------
            // Exportação Governamental
            // ---------------------------
            $exportacao = $this->gerarExportacaoSISAB($atendimento);
            $this->enviarESUS($exportacao);

            return $paciente;

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Falha import e-SUS pré-produção', [
                'erro' => $e->getMessage(),
                'dados' => $dados,
            ]);
            throw $e;
        }
    }

    /**
     * Auditoria detalhada com hash de integridade
     */
    protected function registrarAuditoria($registro, string $acao, ?array $antes, ?array $depois): void
    {
        Auditoria::create([
            'user_id' => auth()->id() ?? null,
            'acao' => $acao,
            'modulo' => 'ESusPreProducaoService',
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

    /**
     * Gera exportação SISAB / e-SUS AB
     */
    public function gerarExportacaoSISAB(Atendimento $atendimento): array
    {
        return [
            'unidade_cnes' => $atendimento->unidade_cnes,
            'data_atendimento' => $atendimento->data_atendimento?->format('Y-m-d'),
            'paciente' => $atendimento->paciente->only(['nome', 'cns', 'cpf', 'data_nascimento', 'sexo']),
            'profissional' => [
                'id' => $atendimento->profissional_id,
            ],
            'cid' => $atendimento->cid,
            'procedimentos' => $atendimento->procedimentos ?? [],
        ];
    }

    /**
     * Envia para e-SUS AB / RNDS
     * ✅ Pré-produção: envia somente se endpoint ativo
     */
    public function enviarESUS(array $dados): bool
    {
        try {
            $endpoint = config('services.esus.preprod_endpoint');
            if (! $endpoint) {
                Log::info('ESus pré-produção desativado, exportação simulada', $dados);

                return true; // apenas simulação
            }

            $response = Http::timeout(15)->post($endpoint, $dados);
            if ($response->successful()) {
                Log::info('ESus pré-produção enviado com sucesso', ['status' => $response->status()]);

                return true;
            }

            Log::error('Falha envio ESUS pré-produção', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;

        } catch (\Exception $e) {
            Log::error('Erro comunicação ESUS pré-produção', ['erro' => $e->getMessage(), 'dados' => $dados]);

            return false;
        }
    }
}
