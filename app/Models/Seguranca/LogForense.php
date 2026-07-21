<?php

declare(strict_types=1);

namespace App\Models\Seguranca;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final class LogForense extends Model
{
    protected $table = 'logs_forenses';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = true;

    protected $guarded = [];

    protected $casts = [

        'payload' => 'array',

        'executado_em' => 'datetime',

        'created_at' => 'datetime',

        'updated_at' => 'datetime',

        'tampered' => 'boolean',
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
                        'uuid' => $model->uuid,
                        'evento' => $model->evento,
                        'executado_em' => now()->toISOString(),
                    ]);
            }
        });

        static::updating(function (): void {

            throw new \RuntimeException(
                'LOG FORENSE É IMUTÁVEL'
            );
        });

        static::deleting(function (): void {

            throw new \RuntimeException(
                'LOG FORENSE NÃO PODE SER REMOVIDO'
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

            'uuid' => $this->uuid,

            'evento' => $this->evento,

            'executado_em' =>
                $this->executado_em?->toISOString(),
        ]);

        return hash_equals(
            $hash,
            $this->current_hash
        );
    }

    public function scopeEvento(
        Builder $query,
        string $evento
    ): Builder {

        return $query->where(
            'evento',
            $evento
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

    public function scopeUsuario(
        Builder $query,
        string $userId
    ): Builder {

        return $query->where(
            'user_id',
            $userId
        );
    }

    public function toAuditArray(): array
    {
        return [

            'id' => $this->id,

            'uuid' => $this->uuid,

            'evento' => $this->evento,

            'user_id' => $this->user_id,

            'ip' => $this->ip,

            'user_agent' => $this->user_agent,

            'session_id' => $this->session_id,

            'current_hash' => $this->current_hash,

            'tampered' => $this->tampered,

            'executado_em' => $this->executado_em,
        ];
    }
}