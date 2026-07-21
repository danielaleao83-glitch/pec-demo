<?php

namespace App\Services\Integracoes;

use App\ModeLs\Paciente;
use App\Services\integracoes\CnsValidatorService;
use App\Services\integracoes\CpfValidatorService;
use Illuminate\Support\Facades\DB;

class SusGovernmentService implements IntegracaoSusInterface
{
    protected $cpfValidator;

    protected $cnsValidator;

    public function __construct(
        CpfValidatorService $cpfValidator,
        CnsValidatorService $cnsValidator
    ) {
        $this->cpfValidator = $cpfValidator;
        $this->cnsValidator = $cnsValidator;
    }

    /**
     * Envia paciente para sistemas oficiais do SUS
     */
    public function enviarPaciente(Paciente $paciente): bool
    {
        // Valida CPF e CNS antes de enviar
        if (! $this->cpfValidator->valida($paciente->getDecrypted('cpf'))) {
            throw new Exception("CPF inválido para paciente {$paciente->id}");
        }

        if (! $this->cnsValidator->valida($paciente->getDecrypted('cns'))) {
            throw new Exception("CNS inválido para paciente {$paciente->id}");
        }

        // Reaproveitar integração existente
        $esusService = new EsusIntegracaoService;
        $datasusService = new DatasusIntegracaoService;

        $resultadoEsus = $esusService->enviarPaciente($paciente);
        $resultadoDatasus = $datasusService->enviarPaciente($paciente);

        // Log seguro em auditoria
        DB::table('pacientes_auditoria')->insert([
            'paciente_id' => $paciente->id,
            'coluna' => 'integracao_sus',
            'valor_anterior' => null,
            'valor_novo' => 'enviado com sucesso',
            'alterado_por' => config('app.usuario_id'),
            'created_at' => now(),
        ]);

        return $resultadoEsus && $resultadoDatasus;
    }
}
