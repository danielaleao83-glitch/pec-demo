<?php

namespace App\Application\DTO;

use DateTimeImmutable;
use InvalidArgumentException;

final class PacienteDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $nome,
        public readonly string $cpf,
        public readonly string $cns,
        public readonly DateTimeImmutable $dataNascimento,
        public readonly ?string $sexo = null,
        public readonly ?string $telefone = null,
        public readonly ?string $email = null,
        public readonly ?string $endereco = null,
        public readonly ?string $status = null,
    ) {}

    public static function fromArray(array $data): self
    {
        self::validate($data);

        return new self(
            id: self::normalizeNullableString($data['id'] ?? null),
            nome: self::normalizeString($data['nome']),
            cpf: self::normalizeString($data['cpf']),
            cns: self::normalizeString($data['cns']),
            dataNascimento: self::parseDate($data['data_nascimento']),
            sexo: self::normalizeNullableString($data['sexo'] ?? null),
            telefone: self::normalizeNullableString($data['telefone'] ?? null),
            email: self::normalizeNullableString($data['email'] ?? null),
            endereco: self::normalizeNullableString($data['endereco'] ?? null),
            status: self::normalizeNullableString($data['status'] ?? null),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'cpf' => $this->cpf,
            'cns' => $this->cns,
            'data_nascimento' => $this->dataNascimento->format('Y-m-d'),
            'sexo' => $this->sexo,
            'telefone' => $this->telefone,
            'email' => $this->email,
            'endereco' => $this->endereco,
            'status' => $this->status,
        ];
    }

    private static function validate(array $data): void
    {
        $required = [
            'nome',
            'cpf',
            'cns',
            'data_nascimento',
        ];

        foreach ($required as $field) {
            if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
                throw new InvalidArgumentException("Campo obrigatório ausente: {$field}");
            }
        }
    }

    private static function parseDate(mixed $value): DateTimeImmutable
    {
        try {
            return new DateTimeImmutable($value);
        } catch (\Exception) {
            throw new InvalidArgumentException("data_nascimento inválida");
        }
    }

    private static function normalizeString(string $value): string
    {
        return trim($value);
    }

    private static function normalizeNullableString(?string $value): ?string
    {
        return $value !== null ? trim($value) : null;
    }
}