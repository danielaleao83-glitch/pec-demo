<?php

declare(strict_types=1);

namespace App\Services\Cnes\Search;

class CnesQueryNormalizer
{
    public function normalize(string $value): string
    {
        $value = mb_strtolower($value);

        $value = preg_replace('/[^\p{L}\p{N}\s]/u', '', $value);

        return trim($value);
    }
}