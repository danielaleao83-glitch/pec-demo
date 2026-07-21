<<?php

namespace App\Modules\Atendimento\Domain\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Sistema\BaseModel;

use App\Modules\Paciente\Domain\Entities\Paciente;
use App\Modules\Auth\Domain\Entities\User;
use App\Modules\Atendimento\Domain\Entities\Atendimento;

use App\Modules\Atendimento\Domain\Enums\TipoEvolucaoEnum;

class Evolucao extends BaseModel
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'evolucoes';

    protected $fillable = [
        'atendimento_id',
        'paciente_id',
        'profissional_id',
        'tipo',
        'descricao',
    ];

    protected $guarded = [
        'id',
        'created_by',
        'updated_by',
        'deleted_at',
    ];

    protected $casts = [
        'tipo' => TipoEvolucaoEnum::class,
        'descricao' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function atendimento(): BelongsTo
    {
        return $this->belongsTo(
            Atendimento::class
        )->withTrashed();
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(
            Paciente::class
        )->withTrashed();
    }

    public function profissional(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        )->withTrashed();
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeDoAtendimento(
        Builder $query,
        int $atendimentoId
    ): Builder {
        return $query->where(
            'atendimento_id',
            $atendimentoId
        );
    }

    public function scopePorPaciente(
        Builder $query,
        int $pacienteId
    ): Builder {
        return $query->where(
            'paciente_id',
            $pacienteId
        );
    }

    public function scopePorProfissional(
        Builder $query,
        int $profissionalId
    ): Builder {
        return $query->where(
            'profissional_id',
            $profissionalId
        );
    }

    public function scopePorTipo(
        Builder $query,
        TipoEvolucaoEnum $tipo
    ): Builder {
        return $query->where(
            'tipo',
            $tipo->value
        );
    }

    public function scopePorPeriodo(
        Builder $query,
        string $inicio,
        string $fim
    ): Builder {
        return $query->whereBetween(
            'created_at',
            [$inicio, $fim]
        );
    }
}