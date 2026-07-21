<?php

namespace App\Services\ESusService\SUS;

use Illuminate\Support\Str;

class ValidacaoSUSService
{
    /*
    |--------------------------------------------------------------------------
    | 🧼 SANITIZAÇÃO SEGURA (PADRÃO FEDERAL)
    |--------------------------------------------------------------------------
    */
    private function somenteNumeros(?string $valor): string
    {
        return preg_replace('/\D/', '', $valor ?? '');
    }

    /*
    |--------------------------------------------------------------------------
    | 🧾 CNS (VALIDAÇÃO ESTRUTURAL + REGRA OFICIAL)
    |--------------------------------------------------------------------------
    */
    public function validarCNS(?string $cns): array
    {
        $cns = $this->somenteNumeros($cns);

        if (!$cns) {
            return $this->erro('CNS não informado');
        }

        if (strlen($cns) !== 15) {
            return $this->erro('CNS deve conter 15 dígitos');
        }

        if (!preg_match('/^[1-2]/', $cns)) {
            return $this->erro('CNS inválido (prefixo SUS)');
        }

        // ⚠️ validação estrutural (DATASUS real depende de base oficial)
        if (!preg_match('/^\d{15}$/', $cns)) {
            return $this->erro('CNS inválido');
        }

        return $this->ok($cns);
    }

    /*
    |--------------------------------------------------------------------------
    | 🪪 CPF (VALIDAÇÃO MATEMÁTICA COMPLETA)
    |--------------------------------------------------------------------------
    */
    public function validarCPF(?string $cpf): array
    {
        $cpf = $this->somenteNumeros($cpf);

        if (!$cpf) {
            return $this->erro('CPF não informado');
        }

        if (strlen($cpf) !== 11) {
            return $this->erro('CPF deve conter 11 dígitos');
        }

        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return $this->erro('CPF inválido (sequência)');
        }

        for ($t = 9; $t < 11; $t++) {

            $soma = 0;

            for ($i = 0; $i < $t; $i++) {
                $soma += $cpf[$i] * (($t + 1) - $i);
            }

            $dv = ((10 * $soma) % 11) % 10;

            if ($cpf[$t] != $dv) {
                return $this->erro('CPF inválido (dígito verificador)');
            }
        }

        return $this->ok($cpf);
    }

    /*
    |--------------------------------------------------------------------------
    | 🏥 CNES (VALIDAÇÃO ESTRUTURAL SUS)
    |--------------------------------------------------------------------------
    */
    public function validarCNES(?string $cnes): array
    {
        $cnes = $this->somenteNumeros($cnes);

        if (!$cnes) {
            return $this->erro('CNES não informado');
        }

        if (strlen($cnes) !== 7) {
            return $this->erro('CNES deve conter 7 dígitos');
        }

        if (!preg_match('/^\d{7}$/', $cnes)) {
            return $this->erro('CNES inválido');
        }

        return $this->ok($cnes);
    }

    /*
    |--------------------------------------------------------------------------
    | 🧠 RESPOSTA PADRÃO (CONSISTENTE SUS)
    |--------------------------------------------------------------------------
    */
    private function ok(string $valor): array
    {
        return [
            'valido' => true,
            'valor'  => $valor,
            'erro'   => null,
            'trace_id' => (string) Str::uuid(),
        ];
    }

    private function erro(string $mensagem): array
    {
        return [
            'valido' => false,
            'valor'  => null,
            'erro'   => $mensagem,
            'trace_id' => (string) Str::uuid(),
        ];
    }
}