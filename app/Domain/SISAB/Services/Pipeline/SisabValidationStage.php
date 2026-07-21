<?php

declare(strict_types=1);

namespace App\Services\ESusService\SISAB\Pipeline;

use App\Services\ESusService\SISAB\Validacao\SisabValidadorService;
use RuntimeException;

class SisabValidationStage
{
    /**
     * 🚀 validação clínica + estrutural SISAB
     */
    public static function handle(array $payload): array
    {
        self::validateStructure($payload);

        SisabValidadorService::validar($payload);

        return self::normalize($payload);
    }

    /**
     * 🔐 valida estrutura mínima obrigatória SISAB
     */
    private static function validateStructure(array $payload): void
    {
        $required = [
            'paciente_uuid',
            'profissional_uuid',
        ];

        foreach ($required as $field) {

            if (!isset($payload[$field])) {
                throw new RuntimeException(
                    "SISAB ValidationStage: campo ausente ({$field})"
                );
            }

            $value = trim((string) $payload[$field]);

            if ($value === '') {
                throw new RuntimeException(
                    "SISAB ValidationStage: campo vazio ({$field})"
                );
            }

            /**
             * 🔐 valida UUID básico
             */
            if (!self::isValidUuid($value)) {
                throw new RuntimeException(
                    "SISAB ValidationStage: UUID inválido ({$field})"
                );
            }
        }
    }

    /**
     * 🧠 normalização profunda segura
     */
    private static function normalize(array $payload): array
    {
        $normalized = [];

        foreach ($payload as $key => $value) {

            if ($value === null) {
                continue;
            }

            if (is_string($value)) {
                $value = trim($value);

                if ($value === '') {
                    continue;
                }
            }

            if (is_array($value)) {
                $value = self::normalize($value);

                if (empty($value)) {
                    continue;
                }
            }

            $normalized[$key] = $value;
        }

        return $normalized;
    }

    /**
     * 🔐 valida UUID (versão simples e segura)
     */
    private static function isValidUuid(string $value): bool
    {
        return (bool) preg_match(
            '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[1-5][0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}$/',
            $value
        );
    }
}