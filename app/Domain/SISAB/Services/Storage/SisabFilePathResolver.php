<?php

declare(strict_types=1);

namespace App\Services\ESusService\SISAB\Storage;

use Illuminate\Support\Str;

class SisabFilePathResolver
{
    /**
     * 🔐 Resolve path seguro com isolamento e anti-colisão
     */
    public function resolve(
        string $basePath,
        string $xmlUuid
    ): array {

        $timestamp = now()->format('Ymd_His_u');

        /**
         * 🧬 shard por UUID (reduz colisão e melhora organização)
         */
        $shard = substr($xmlUuid, 0, 2);

        /**
         * 🧠 estrutura hierárquica SISAB
         */
        $directory = $basePath . DIRECTORY_SEPARATOR . $shard;

        $filename = sprintf(
            '%s_%s.xml',
            $timestamp,
            $xmlUuid
        );

        $path = $directory . DIRECTORY_SEPARATOR . $filename;

        return [
            'directory' => $directory,
            'filename' => $filename,
            'path' => $path,
            'tmp_path' => $path . '.tmp',
            'shard' => $shard,
        ];
    }
}