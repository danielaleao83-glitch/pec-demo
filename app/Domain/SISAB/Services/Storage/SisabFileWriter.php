<?php

declare(strict_types=1);

namespace App\Services\ESusService\SISAB\Storage;

use RuntimeException;

class SisabFileWriter
{
    /**
     * 🔐 Permissão padrão SISAB
     */
    private const FILE_PERMISSION = 0640;

    /**
     * 🚀 Escrita atômica com validação de integridade
     */
    public function writeAtomic(
        string $tmpPath,
        string $content,
        string $finalPath
    ): void {

        $this->writeTempFile($tmpPath, $content);
        $this->validateTempFile($tmpPath);
        $this->moveAtomically($tmpPath, $finalPath);
        $this->applyPermissions($finalPath);
        $this->validateFinalFile($finalPath);
    }

    /**
     * 🧾 escrita temporária segura
     */
    private function writeTempFile(string $tmpPath, string $content): void
    {
        $written = file_put_contents($tmpPath, $content, LOCK_EX);

        if ($written === false || $written === 0) {
            throw new RuntimeException('Falha ao escrever arquivo temporário SISAB');
        }
    }

    /**
     * 🔐 validação pós-escrita temporária
     */
    private function validateTempFile(string $tmpPath): void
    {
        if (!file_exists($tmpPath)) {
            throw new RuntimeException('Arquivo temporário não existe');
        }

        if (filesize($tmpPath) <= 0) {
            throw new RuntimeException('Arquivo temporário corrompido');
        }
    }

    /**
     * 🔒 move atômico
     */
    private function moveAtomically(string $tmpPath, string $finalPath): void
    {
        if (!rename($tmpPath, $finalPath)) {
            throw new RuntimeException('Falha ao mover XML SISAB (atomic rename)');
        }
    }

    /**
     * 🔐 permissões seguras
     */
    private function applyPermissions(string $finalPath): void
    {
        if (!chmod($finalPath, self::FILE_PERMISSION)) {
            throw new RuntimeException('Falha ao aplicar permissões SISAB');
        }
    }

    /**
     * 🧬 validação pós-gravação (integridade final)
     */
    private function validateFinalFile(string $finalPath): void
    {
        if (!file_exists($finalPath)) {
            throw new RuntimeException('Arquivo final não existe');
        }

        if (filesize($finalPath) <= 0) {
            throw new RuntimeException('Arquivo final corrompido');
        }

        if (!is_readable($finalPath)) {
            throw new RuntimeException('Arquivo final não é legível');
        }
    }
}