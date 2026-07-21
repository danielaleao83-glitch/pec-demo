<?php

namespace App\Services\Integracoes;

use App\Services\Integracoes\CnsValidatorService;
use App\Services\Interfaces\Integracoes\CpfValidatorServiceInterface;

class CpfValidatorService implements CpfValidatorServiceInterface
{
    public function validar(string $cpf): bool
    {
        // 🔐 Sanitiza
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        // 🚫 Regras básicas
        if (strlen($cpf) !== 11 || preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // 🔢 Validação dos dígitos
        for ($t = 9; $t < 11; $t++) {

            $soma = 0;

            for ($i = 0; $i < $t; $i++) {
                $soma += $cpf[$i] * (($t + 1) - $i);
            }

            $digito = ((10 * $soma) % 11) % 10;

            if ((int) $cpf[$t] !== $digito) {
                return false;
            }
        }

        return true;
    }
}