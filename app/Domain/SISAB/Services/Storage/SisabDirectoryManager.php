<?php

declare(strict_types=1);

namespace App\Services\ESusService\SISAB\Storage;

use Illuminate\Support\Facades\File;
use RuntimeException;

class SisabDirectoryManager
{
    /**
     * 🔐 Base path isolado por ambiente
     */
    private string $basePath;

    public function __construct()
    {
        $this->basePath = $this->resolveBasePath();
    }

    /**
     * 🚀 garante diretório SISAB seguro
     */
    public function ensure(): void
    {
        $this->validateBasePath();

        if (!File::exists($this->basePath)) {
            File::makeDirectory(
                $this->basePath,
                0750,
                true
            );
        }

        $this->validateWritable();
    }

    /**
     * 📂 retorna path base
     */
    public function path(): string
    {
        return $this->basePath;
    }

    /**
     * 🧠 resolve ambiente isolado
     */
    private function resolveBasePath(): string
    {
        return storage_path(
            'app/sisab/' . app()->environment()
        );
    }

    /**
     * 🔐 valida path base
     */
    private function validateBasePath(): void
    {
        if (str_contains($this->basePath, '..')) {
            throw new RuntimeException('Path SISAB inválido');
        }

        if (!is_string($this->basePath)) {
            throw new RuntimeException('Base path SISAB inválido');
        }
    }

    /**
     * 🔐 garante escrita real (não só existência)
     */
    private function validateWritable(): void
    {
        if (!is_dir($this->basePath)) {
            throw new RuntimeException('Diretório SISAB não existe');
        }

        if (!is_writable($this->basePath)) {
            throw new RuntimeException('Diretório SISAB não é gravável');
        }
    }
}