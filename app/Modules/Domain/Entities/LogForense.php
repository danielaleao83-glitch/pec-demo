<?php

namespace App\Modules\Auditoria\Domain\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Modules\Auth\Domain\Entities\User;
use App\Modules\Paciente\Domain\Entities\Paciente;

use App\Modules\Auditoria\Domain\Enums\AcaoForenseEnum;

class LogForense extends Model
{
    protected $table = 'logs_forense';

    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'paciente_id',
        'acao',
        'rota',
        'metodo',
        'ip',
        'user_agent',
        'dados_anteriores',
        'dados_novos',
        'hash',
        'hash_anterior',
    ];

    protected $casts = [
        'dados_anteriores' => 'array',
        'dados_novos' => 'array',
        'acao' => AcaoForenseEnum::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        )->withTrashed();
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(
            Paciente::class,
            'paciente_id'
        )->withTrashed();
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */

    public function setIpAttribute(?string $value): void
    {
        if ($value) {
            $this->attributes['ip'] =
                hash_hmac(
                    'sha256',
                    $value,
                    config('app.key')
                );
        }
    }

    public function setUserAgentAttribute(
        ?string $value
    ): void {
        if ($value) {
            $this->attributes['user_agent'] =
                strip_tags($value);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | APPEND ONLY
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::updating(fn () => false);

        static::deleting(fn () => false);
    }
}