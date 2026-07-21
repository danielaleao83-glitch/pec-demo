<?php

declare(strict_types=1);

namespace App\Domain\Paciente\Traits;

trait HasPacienteIntegrity
{
    private string $hashIntegridade;

    public function bootIntegrity(): void
    {
        $this->refreshIntegrity();
    }

    public function refreshIntegrity(): void
    {
        $this->hashIntegridade = hash(
            'sha256',
            serialize($this)
        );
    }

    public function integrityHash(): string
    {
        return $this->hashIntegridade;
    }
}
