<?php

namespace App\Services\Paciente;

use App\Models\Paciente\Paciente;
use App\Models\RegistroMultiprofissional\CrescimentoInfantil;
use App\Models\RegistroMultiprofissional\Encaminhamento;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class PacienteService
{
    /*
    |----------------------------------------------------------------------
    | VALIDAÇÃO DE DOMÍNIO
    |----------------------------------------------------------------------
    */
    public function validarPaciente(Paciente $paciente): void
    {
        if (empty($paciente->nome)) {
            throw new \DomainException('Nome do paciente é obrigatório');
        }

        if (empty($paciente->data_nascimento)) {
            throw new \DomainException('Data de nascimento é obrigatória');
        }

        if (! in_array($paciente->sexo, ['M', 'F'])) {
            throw new \DomainException('Sexo inválido');
        }
    }

    /*
    |----------------------------------------------------------------------
    | ENCAMINHAMENTO
    |----------------------------------------------------------------------
    */
    public function criarEncaminhamento(array $dados, ?int $userId = null): Encaminhamento
    {
        try {

            return DB::transaction(function () use ($dados, $userId) {

                $encaminhamento = new Encaminhamento($dados);

                $encaminhamento->validarCamposObrigatorios();
                $encaminhamento->save();

                // 🔐 Auditoria estruturada
                Log::channel('audit')->info('encaminhamento_criado', [
                    'registro_id' => $encaminhamento->id,
                    'user_id' => $userId,
                    'dados' => $this->sanitizar($dados),
                ]);

                return $encaminhamento;
            });

        } catch (Throwable $e) {

            Log::channel('security')->error('erro_criar_encaminhamento', [
                'erro' => $e->getMessage(),
                'dados' => $this->sanitizar($dados),
            ]);

            throw $e;
        }
    }

    /*
    |----------------------------------------------------------------------
    | CRESCIMENTO INFANTIL
    |----------------------------------------------------------------------
    */
    public function criarCrescimentoInfantil(array $dados, ?int $userId = null): CrescimentoInfantil
    {
        try {

            return DB::transaction(function () use ($dados, $userId) {

                $crescimento = new CrescimentoInfantil($dados);

                $crescimento->validarCamposObrigatorios();
                $crescimento->save();

                Log::channel('audit')->info('crescimento_infantil_criado', [
                    'registro_id' => $crescimento->id,
                    'user_id' => $userId,
                    'dados' => $this->sanitizar($dados),
                ]);

                return $crescimento;
            });

        } catch (Throwable $e) {

            Log::channel('security')->error('erro_crescimento_infantil', [
                'erro' => $e->getMessage(),
                'dados' => $this->sanitizar($dados),
            ]);

            throw $e;
        }
    }

    /*
    |----------------------------------------------------------------------
    | LGPD - SANITIZAÇÃO
    |----------------------------------------------------------------------
    */
    protected function sanitizar(array $dados): array
    {
        $sensíveis = [
            'cpf', 'cns', 'cartao_sus', 'senha', 'password',
        ];

        foreach ($dados as $key => $value) {

            if (in_array(strtolower($key), $sensíveis)) {
                $dados[$key] = '***PROTEGIDO***';
            }

            if (is_string($value) && strlen($value) > 1000) {
                $dados[$key] = substr($value, 0, 1000).'...';
            }
        }

        return $dados;
    }
}
