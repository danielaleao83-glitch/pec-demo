<?php

declare(strict_types=1);

namespace App\Services\ESusService\SISAB\Pipeline;

use App\Services\ESusService\SISAB\Sanitizacao\SisabSanitizadorService;
use RuntimeException;

class SisabSanitizationStage
{
    /**
     * 🔐 limite de profundidade recursiva
     */
    private const MAX_DEPTH = 5;

    /**
     * 🚀 sanitização clínica SISAB (profunda + recursiva)
     */
    public static function handle(array $payload): array
    {
        $payload = SisabSanitizadorService::limpar($payload);

        return self::normalize($payload, 0);
    }

    /**
     * 🧠 normalização pós-sanitização
     */
    private static function normalize(array $payload, int $depth): array
    {
        if ($depth > self::MAX_DEPTH) {
            throw new RuntimeException(
                'Profundidade de payload SISAB excedida'
            );
        }

        $normalized = [];

        foreach ($payload as $key => $value) {

            /**
             * 🔐 normaliza chave (schema consistente)
             */
            $key = self::normalizeKey((string) $key);

            /**
             * 🚫 remove null
             */
            if ($value === null) {
                continue;
            }

            /**
             * 🧾 string cleanup
             */
            if (is_string($value)) {

                $value = trim($value);

                $value = preg_replace('/\s+/u', ' ', $value);

                if ($value === '') {
                    continue;
                }
            }

            /**
             * 🚫 bloqueio de tipos perigosos
             */
            if (is_object($value)) {
                throw new RuntimeException('Objeto não permitido no SISAB');
            }

            if (is_resource($value)) {
                throw new RuntimeException('Resource não permitido no SISAB');
            }

            /**
             * 🔁 recursão controlada
             */
            if (is_array($value)) {
                $value = self::normalize($value, $depth + 1);

                if (empty($value)) {
                    continue;
                }
            }

            $normalized[$key] = $value;
        }

        return $normalized;
    }

    /**
     * 🔐 normalização de chave (padrão SISAB)
     */
    private static function normalizeKey(string $key): string
    {
        $key = strtolower($key);
        $key = str_replace(['-', ' '], '_', $key);
        $key = preg_replace('/[^a-z0-9_]/', '', $key);

        return $key;
    }
}