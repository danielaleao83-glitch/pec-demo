<?php

namespace App\Application\DTO;

use DateTimeImmutable;
use InvalidArgumentException;

final class AtendimentoDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $pacienteId,
        public readonly string $profissionalId,
        public readonly string $unidadeId,
        public readonly string $tipoAtendimento,
        public readonly DateTimeImmutable $dataHora,
        public readonly ?string $descricao = null,
        public readonly ?string $status = null,
    ) {}

    public static function fromArray(array $data): self
    {
        self::validate($data);

        return new self(
            id: self::normalizeNullableString($data['id'] ?? null),
            pacienteId: self::normalizeString($data['paciente_id']),
            profissionalId: self::normalizeString($data['profissional_id']),
            unidadeId: self::normalizeString($data['unidade_id']),
            tipoAtendimento: self::normalizeString($data['tipo_atendimento']),
            dataHora: self::parseDateTime($data['data_hora']),
            descricao: self::normalizeNullableString($data['descricao'] ?? null),
            status: self::normalizeNullableString($data['status'] ?? null),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'paciente_id' => $this->pacienteId,
            'profissional_id' => $this->profissionalId,
            'unidade_id' => $this->unidadeId,
            'tipo_atendimento' => $this->tipoAtendimento,
            'data_hora' => $this->dataHora->format(DATE_ATOM),
            'descricao' => $this->descricao,
            'status' => $this->status,
        ];
    }

    private static function validate(array $data): void
    {
        $required = [
            'paciente_id',
            'profissional_id',
            'unidade_id',
            'tipo_atendimento',
            'data_hora',
        ];

        foreach ($required as $field) {
            if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
                throw new InvalidArgumentException("Campo obrigatório ausente: {$field}");
            }
        }
    }

    private static function parseDateTime(mixed $value): DateTimeImmutable
    {
        try {
            return new DateTimeImmutable($value);
        } catch (\Exception) {
            throw new InvalidArgumentException("data_hora inválida");
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