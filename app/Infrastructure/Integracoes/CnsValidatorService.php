<?php

namespace App\Services\Integracoes;

class CnsValidatorService
{
    public function validar(string $cns): bool
    {
        $cns = preg_replace('/[^0-9]/', '', $cns);

        if (strlen($cns) != 15) {
            return false;
        }

        // CNS provisório começa com 7,8,9
        if (in_array($cns[0], ['7', '8', '9'])) {
            return $this->validarProvisorio($cns);
        }

        return $this->validarDefinitivo($cns);
    }

    private function validarDefinitivo(string $cns): bool
    {
        $pis = substr($cns, 0, 11);
        $soma = 0;

        for ($i = 0; $i < 11; $i++) {
            $soma += $pis[$i] * (15 - $i);
        }

        $resto = $soma % 11;
        $dv = 11 - $resto;

        if ($dv == 11) {
            $dv = 0;
        }
        if ($dv == 10) {
            return false;
        }

        return substr($cns, 11, 4) == $dv.'000';
    }

    private function validarProvisorio(string $cns): bool
    {
        $soma = 0;

        for ($i = 0; $i < 15; $i++) {
            $soma += $cns[$i] * (15 - $i);
        }

        return $soma % 11 == 0;
    }
}
