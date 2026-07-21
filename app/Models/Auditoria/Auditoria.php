<?php

declare(strict_types=1);

namespace App\Models\Auditoria;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final class Auditoria extends Model
{
    protected $table = 'auditorias';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    public $timestamps = true;

    protected $casts = [

        'payload' => 'array',

        'dados_antes' => 'array',

        'dados_depois' => 'array',

        'executado_em' => 'datetime',

        'tampered' => 'boolean',

        'created_at' => 'datetime',

        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {

            if (!$model->id) {
                $model->id = (string) Str::uuid();
            }

            if (!$model->uuid) {
                $model->uuid = (string) Str::uuid();
            }

            if (!$model->current_hash) {
                $model->current_hash =
                    self::gerarHash([
                        'id' => $model->id,
                        'modulo' => $model->modulo,
                        'acao' => $model->acao,
                        'executado_em' => now()->toISOString(),
                    ]);
            }
        });

        static::updating(function (): void {
            throw new Exception(
                'AUDITORIA É IMUTÁVEL'
            );
        });

        static::deleting(function (): void {
            throw new Exception(
                'AUDITORIA NÃO PODE SER REMOVIDA'
            );
        });
    }

    public static function gerarHash(
        array $dados
    ): string {

        ksort($dados);

        return hash(
            'sha256',
            json_encode(
                $dados,
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
            )
        );
    }

    public function validarIntegridade(): bool
    {
        $hash = self::gerarHash([
            'id' => $this->id,
            'modulo' => $this->modulo,
            'acao' => $this->acao,
            'executado_em' => $this->executado_em?->toISOString(),
        ]);

        return hash_equals(
            $hash,
            $this->current_hash
        );
    }

    public function scopeCorrelation(
        Builder $query,
        string $correlationId
    ): Builder {
        return $query->where(
            'correlation_id',
            $correlationId
        );
    }

    public function scopeModulo(
        Builder $query,
        string $modulo
    ): Builder {
        return $query->where(
            'modulo',
            $modulo
        );
    }

    public function scopeAcao(
        Builder $query,
        string $acao
    ): Builder {
        return $query->where(
            'acao',
            $acao
        );
    }

    public function scopeViolados(
        Builder $query
    ): Builder {
        return $query->where(
            'tampered',
            true
        );
    }
}