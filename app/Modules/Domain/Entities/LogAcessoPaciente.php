<?php

namespace App\Modules\Auditoria\Domain\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Modules\Auth\Domain\Entities\User;
use App\Modules\Paciente\Domain\Entities\Paciente;

use App\Modules\Auditoria\Domain\Enums\AcaoLogPacienteEnum;

class LogAcessoPaciente extends Model
{
    use HasFactory;

    protected $table = 'log_acesso_pacientes';

    protected $fillable = [
        'user_id',
        'paciente_id',
        'acao',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'acao' => AcaoLogPacienteEnum::class,
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

    public function setUserAgentAttribute(?string $value): void
    {
        if ($value) {
            $this->attributes['user_agent'] =
                strip_tags($value);
        }
    }
}