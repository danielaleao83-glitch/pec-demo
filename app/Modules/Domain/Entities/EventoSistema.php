<<?php

namespace App\Modules\Auditoria\Domain\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

use App\Modules\Auditoria\Domain\Enums\TipoEventoEnum;

class EventoSistema extends Model
{
    use HasUuids;

    protected $table = 'eventos_sistema';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'entidade',
        'entidade_id',
        'tipo_evento',
        'payload',
        'hash',
        'hash_anterior',
    ];

    protected $casts = [
        'payload' => 'array',
        'tipo_evento' => TipoEventoEnum::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | PROTEÇÃO APPEND-ONLY
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::updating(function () {
            return false;
        });

        static::deleting(function () {
            return false;
        });
    }
}