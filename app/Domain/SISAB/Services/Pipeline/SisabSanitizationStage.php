<?php

declare(strict_types=1);

namespace App\Services\ESusService\SISAB\Pipeline;

use App\Services\ESusService\SISAB\Sanitizacao\SisabSanitizadorService;
use RuntimeException;

class SisabSanitizationStage
{
    /**
     * 🔐 LIMITE DE PROFUNDIDADE
     */
    private const MAX_DEPTH = 5;

    /**
     * 🚀 FIREWALL DE DADOS SISAB
     */
    public static function handle(array $payload): array
    {
        $payload = SisabSanitizadorService::limpar($payload);

        return self::normalize($payload, 0);
    }

    /**
     * 🧠 NORMALIZAÇÃO FORTE + PROTEÇÃO RECURSIVA
     */
    private static function normalize(array $payload, int $depth): array
    {
        if ($depth > self::MAX_DEPTH) {
            throw new RuntimeException(
                'Profundidade de payload SISAB excedida'
            );
        }

        $clean = [];

        foreach ($payload as $key => $value) {

            /**
             * 🔐 NORMALIZA CHAVE
             */
            $key = self::normalizeKey((string) $key);

            /**
             * 🚫 REMOVE NULL
             */
            if ($value === null) {
                continue;
            }

            /**
             * 🧾 STRING HARD SANITIZATION
             */
            if (is_string($value)) {

                $value = trim($value);

                $value = preg_replace('/\s+/u', ' ', $value);

                $value = preg_replace('/[^\PC\s]/u', '', $value);

                if ($value === '') {
                    continue;
                }
            }

            /**
             * 🔁 RECURSÃO SEGURA
             */
            if (is_array($value)) {

                $value = self::normalize($value, $depth + 1);

                if (empty($value)) {
                    continue;
                }
            }

            /**
             * 🚫 BLOQUEIO DE TIPOS SUSPEITOS
             */
            if (is_object($value)) {
                throw new RuntimeException(
                    'Objeto não permitido no payload SISAB'
                );
            }

            if (is_resource($value)) {
                throw new RuntimeException(
                    'Resource não permitido no payload SISAB'
                );
            }

            $clean[$key] = $value;
        }

        return $clean;
    }

    /**
     * 🔐 NORMALIZAÇÃO DE CHAVES (PADRÃO SISAB)
     */
    private static function normalizeKey(string $key): string
    {
        $key = strtolower($key);

        $key = str_replace(
            ['-', ' '],
            '_',
            $key
        );

        $key = preg_replace('/[^a-z0-9_]/', '', $key);

        return $key;
    }
}