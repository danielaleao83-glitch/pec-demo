<?php

declare(strict_types=1);

namespace App\Domain\Paciente\Entities;

use App\Domain\Paciente\Enums\SexoEnum;
use App\Domain\Paciente\Enums\StatusPacienteEnum;
use App\Domain\Paciente\Events\PacienteAtualizadoEvent;
use App\Domain\Paciente\Events\PacienteCriadoEvent;
use App\Domain\Paciente\Traits\HasPacienteEvents;
use App\Domain\Paciente\Traits\HasPacienteIntegrity;
use App\Domain\Paciente\ValueObjects\CNS;
use App\Domain\Paciente\ValueObjects\CPF;
use App\Domain\Paciente\ValueObjects\DataNascimento;
use App\Domain\Paciente\ValueObjects\NomePaciente;
use App\Domain\Paciente\ValueObjects\PacienteId;
use App\Domain\Paciente\ValueObjects\Telefone;
use Ramsey\Uuid\Uuid;

class Paciente
{
    use HasPacienteEvents;
    use HasPacienteIntegrity;

    private string $uuid;

    private StatusPacienteEnum $status;

    public function __construct(
        private PacienteId $id,
        private CNS $cns,
        private CPF $cpf,
        private NomePaciente $nome,
        private DataNascimento $dataNascimento,
        private SexoEnum $sexo,
        private ?Telefone $telefone = null,
    ) {

        $this->uuid = Uuid::uuid7()->toString();

        $this->status = StatusPacienteEnum::ATIVO;

        $this->bootIntegrity();

        $this->recordEvent(
            new PacienteCriadoEvent(
                pacienteId: $this->id->value(),
                uuid: $this->uuid,
            )
        );
    }

    public function atualizarTelefone(
        Telefone $telefone
    ): void {

        $this->telefone = $telefone;

        $this->refreshIntegrity();

        $this->recordEvent(
            new PacienteAtualizadoEvent(
                pacienteId: $this->id->value()
            )
        );
    }

    public function desativar(): void
    {
        $this->status = StatusPacienteEnum::INATIVO;

        $this->refreshIntegrity();
    }

    public function id(): PacienteId
    {
        return $this->id;
    }

    public function uuid(): string
    {
        return $this->uuid;
    }

    public function cns(): CNS
    {
        return $this->cns;
    }

    public function cpf(): CPF
    {
        return $this->cpf;
    }

    public function nome(): NomePaciente
    {
        return $this->nome;
    }

    public function dataNascimento(): DataNascimento
    {
        return $this->dataNascimento;
    }

    public function sexo(): SexoEnum
    {
        return $this->sexo;
    }

    public function telefone(): ?Telefone
    {
        return $this->telefone;
    }

    public function status(): StatusPacienteEnum
    {
        return $this->status;
    }

    public function toArray(): array
    {
        return [

            'id' => $this->id->value(),

            'uuid' => $this->uuid,

            'cns' => $this->cns->value(),

            'cpf' => $this->cpf->masked(),

            'nome' => $this->nome->value(),

            'sexo' => $this->sexo->value,

            'status' => $this->status->value,

            'telefone' => $this->telefone?->value(),

            'data_nascimento' => $this->dataNascimento
                ->value()
                ->format('Y-m-d'),

            'hash_integridade' => $this->integrityHash(),
        ];
    }
}
