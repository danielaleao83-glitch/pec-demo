<?php

namespace App\Modules\Assistencial\Domain\Entities;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\Sistema\BaseModel;

use App\Modules\Paciente\Domain\Entities\Paciente;
use App\Modules\Atendimento\Domain\Entities\Atendimento;

class AnexoClinico extends BaseModel
{
    use SoftDeletes;

    protected $table = 'anexos_clinicos';

    protected $fillable = [
        'paciente_id',
        'atendimento_id',
        'tipo',
        'descricao',
        'arquivo',
        'assinatura_medico',
    ];

    protected $casts = [
        'paciente_id' => 'integer',
        'atendimento_id' => 'integer',
        'assinatura_medico' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(
            Paciente::class
        )->withTrashed();
    }

    public function atendimento(): BelongsTo
    {
        return $this->belongsTo(
            Atendimento::class
        )->withTrashed();
    }
}