<?php

declare(strict_types=1);

namespace App\Models\Auditoria;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

final class EventStore extends Model
{
    protected $table = 'event_store';

    public $timestamps = true;

    protected $fillable = [
        'aggregate_id',
        'aggregate_type',
        'event_id',
        'event_type',
        'event_version',

        'payload',
        'metadata',

        'user_id',
        'unit_id',

        'ip',
        'user_agent',
        'session_id',

        'correlation_id',
        'causation_id',

        'previous_hash',
        'current_hash',

        'signature',

        'tampered',

        'occurred_at',
    ];

    protected $casts = [
        'payload'     => 'array',
        'metadata'    => 'array',
        'tampered'    => 'boolean',
        'occurred_at' => 'datetime',
    ];

    /**
     * =========================================================
     * IMUTABILIDADE
     * =========================================================
     */
    protected static function booted(): void
    {
        static::updating(function (): void {
            throw new Exception(
                'EVENT STORE É IMUTÁVEL.'
            );
        });

        static::deleting(function (): void {
            throw new Exception(
                'EVENT STORE NÃO PODE SER DELETADO.'
            );
        });
    }

    /**
     * =========================================================
     * RELACIONAMENTOS
     * =========================================================
     */

    public function user()
    {
        return $this->belongsTo(
            \App\Models\User::class
        );
    }

    /**
     * =========================================================
     * SCOPES
     * =========================================================
     */

    public function scopePorTipo(
        Builder $query,
        string $tipo
    ): Builder {
        return $query->where(
            'event_type',
            $tipo
        );
    }

    public function scopePorAggregate(
        Builder $query,
        string $aggregateId
    ): Builder {
        return $query->where(
            'aggregate_id',
            $aggregateId
        );
    }

    public function scopePorCorrelation(
        Builder $query,
        string $correlationId
    ): Builder {
        return $query->where(
            'correlation_id',
            $correlationId
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

    /**
     * =========================================================
     * HASH CHAIN
     * =========================================================
     */

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
        $dados = [
            'aggregate_id'   => $this->aggregate_id,
            'aggregate_type' => $this->aggregate_type,
            'event_id'       => $this->event_id,
            'event_type'     => $this->event_type,
            'event_version'  => $this->event_version,
            'payload'        => $this->payload,
            'occurred_at'    => $this->occurred_at?->toISOString(),
            'previous_hash'  => $this->previous_hash,
        ];

        $hashCalculado =
            self::gerarHash($dados);

        return hash_equals(
            $hashCalculado,
            $this->current_hash
        );
    }

    /**
     * =========================================================
     * DETECÇÃO DE FRAUDE
     * =========================================================
     */

    public function marcarComoViolado(): void
    {
        DB::table($this->table)
            ->where('id', $this->id)
            ->update([
                'tampered' => true,
            ]);
    }

    public function verificar(): bool
    {
        $ok = $this->validarIntegridade();

        if (! $ok) {
            $this->marcarComoViolado();
        }

        return $ok;
    }

    /**
     * =========================================================
     * EXPORTAÇÃO AUDITORIA
     * =========================================================
     */

    public function toAuditArray(): array
    {
        return [

            'id' => $this->id,

            'aggregate_id'
                => $this->aggregate_id,

            'aggregate_type'
                => $this->aggregate_type,

            'event_id'
                => $this->event_id,

            'event_type'
                => $this->event_type,

            'event_version'
                => $this->event_version,

            'correlation_id'
                => $this->correlation_id,

            'causation_id'
                => $this->causation_id,

            'user_id'
                => $this->user_id,

            'unit_id'
                => $this->unit_id,

            'ip'
                => $this->ip,

            'session_id'
                => $this->session_id,

            'occurred_at'
                => $this->occurred_at,

            'previous_hash'
                => $this->previous_hash,

            'current_hash'
                => $this->current_hash,

            'signature'
                => $this->signature,

            'tampered'
                => $this->tampered,
        ];
    }
}