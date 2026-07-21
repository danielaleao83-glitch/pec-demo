<?php

namespace App\Services\Integracoes;

use App\Models\Paciente;

class SusIntegracaoService
{
    protected $cnsValidator;

    protected $cpfValidator;

    protected $logService;

    protected $esusService;

    public function __construct(
        CnsValidatorService $cnsValidator,
        CpfValidatorService $cpfValidator,
        SusLogService $logService,
        EsusIntegracaoService $esusService
    ) {
        $this->cnsValidator = $cnsValidator;
        $this->cpfValidator = $cpfValidator;
        $this->logService = $logService;
        $this->esusService = $esusService;
    }

    /**
     * Envio seguro de paciente para o SUS.
     *
     * @return array
     */
    public function enviarPaciente(Paciente $paciente)
    {
        // 🔒 Status inicial
        $status = [
            'success' => false,
            'mensagem' => '',
            'erro' => null,
        ];

        // Validar CNS
        if (! $this->cnsValidator->validar($paciente->cns)) {
            $status['mensagem'] = 'CNS inválido';
            $this->logService->logFalha($paciente->id, 'CNS inválido');

            return $status;
        }

        // Validar CPF
        if (! $this->cpfValidator->validar($paciente->cpf)) {
            $status['mensagem'] = 'CPF inválido';
            $this->logService->logFalha($paciente->id, 'CPF inválido');

            return $status;
        }

        // 🔒 Transação segura
        DB::beginTransaction();
        try {
            // Preparar payload (reaproveitando EsusIntegracaoService)
            $payload = $this->esusService->formatarPaciente($paciente);

            // Enviar para o SUS (API e-SUS)
            $resposta = $this->esusService->enviarPaciente($payload);

            if ($resposta['success'] ?? false) {
                // ✅ Log de sucesso com hash do payload
                $this->logService->logSucesso(
                    $paciente->id,
                    hash('sha256', json_encode($payload))
                );

                DB::commit();
                $status['success'] = true;
                $status['mensagem'] = 'Paciente enviado com sucesso';
            } else {
                // ❌ Log de falha detalhado
                $this->logService->logFalha(
                    $paciente->id,
                    $resposta['mensagem'] ?? 'Erro desconhecido'
                );
                DB::rollBack();
                $status['mensagem'] = 'Falha ao enviar paciente';
                $status['erro'] = $resposta['mensagem'] ?? 'Sem detalhes';
            }

        } catch (\Exception $e) {
            DB::rollBack();
            // 🔒 Log de exceção
            $this->logService->logErroException($paciente->id, $e);
            $status['mensagem'] = 'Erro crítico na integração';
            $status['erro'] = $e->getMessage();
        }

        return $status;
    }

    /**
     * Envio em lote de pacientes
     */
    public function enviarPacientesLote(array $pacientes)
    {
        $resultados = [];
        foreach ($pacientes as $paciente) {
            $resultados[$paciente->id] = $this->enviarPaciente($paciente);
        }

        return $resultados;
    }
}
