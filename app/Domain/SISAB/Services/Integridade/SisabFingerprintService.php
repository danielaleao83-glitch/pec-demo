<?php

declare(strict_types=1);

namespace App\Services\ESusService\SISAB\Integridade;

class SisabFingerprintService
{
    /**
     * 🚀 gera fingerprint determinístico SISAB (nível federal)
     */
    public static function gerar(array $dados): string
    {
        $normalized = self::normalize($dados);

        return hash(
            'sha256',
            implode('|', $normalized)
        );
    }

    /**
     * 🧠 normalização determinística forte
     */
    private static function normalize(array $dados): array
    {
        $keys = [
            'paciente_uuid',
            'profissional_uuid',
            'unidade_uuid',
            'descricao',
        ];

        $output = [];

        foreach ($keys as $key) {

            $value = $dados[$key] ?? '';

            if (is_array($value)) {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }

            $value = (string) $value;

            $value = trim($value);

            $value = mb_strtolower($value, 'UTF-8');

            $output[] = $value;
        }

        return $output;
    }
}