<?php

declare(strict_types=1);

namespace App\Services\ESusService\SISAB\Sanitizacao;

class SisabSanitizadorService
{
    /**
     * 🚀 entrada principal
     */
    public static function limpar(array $dados): array
    {
        return self::limparRecursivo($dados);
    }

    /**
     * 🔁 sanitização recursiva (nível e-SUS real)
     */
    private static function limparRecursivo(mixed $data): mixed
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::limparRecursivo($value);
            }
            return $data;
        }

        if (is_string($data)) {
            return self::sanitizarString($data);
        }

        return $data;
    }

    /**
     * 🔐 sanitização de string nível hospitalar
     */
    private static function sanitizarString(string $value): string
    {
        // 1. remove caracteres invisíveis perigosos
        $value = preg_replace('/[^\PC\s]/u', '', $value);

        // 2. remove controle XML perigoso
        $value = str_replace(
            ['<', '>', '&', '"', "'"],
            [' ', ' ', ' e ', ' ', ' '],
            $value
        );

        // 3. normaliza encoding
        $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');

        // 4. trim seguro
        $value = trim($value);

        // 5. remove múltiplos espaços
        $value = preg_replace('/\s+/u', ' ', $value);

        // 6. hard limit hospitalar (evita payload abuse)
        return mb_substr($value, 0, 5000);
    }
}