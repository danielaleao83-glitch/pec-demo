<?php

declare(strict_types=1);

namespace App\Services\Cnes\Indexer;

use App\Models\Estabelecimentos\Cnes;

class CnesIndexBuilder
{
    /**
     * 🧠 Constrói documento para indexação
     */
    public function build(Cnes $cnes): array
    {
        return [
            'id' => $cnes->id,
            'cnes' => $cnes->cnes,

            /*
            |--------------------------------------------------------------------------
            | 🧠 NORMALIZAÇÃO INSTITUCIONAL
            |--------------------------------------------------------------------------
            */
            'nome' => $this->normalize($cnes->nome),
            'nome_raw' => $cnes->nome,

            'municipio' => $this->normalize($cnes->municipio),
            'estado' => strtoupper($cnes->estado),

            'tipo' => $cnes->tipo_estabelecimento,

            /*
            |--------------------------------------------------------------------------
            | 🔎 SEARCH BOOST KEYS
            |--------------------------------------------------------------------------
            */
            'keywords' => $this->generateKeywords($cnes),
        ];
    }

    private function normalize(string $value): string
    {
        $value = mb_strtolower($value);

        $value = preg_replace('/[^\p{L}\p{N}\s]/u', '', $value);

        return trim($value);
    }

    private function generateKeywords(Cnes $cnes): array
    {
        return array_values(array_filter([
            $cnes->cnes,
            $this->normalize($cnes->nome),
            $this->normalize($cnes->municipio),
            strtoupper($cnes->estado),
        ]));
    }
}