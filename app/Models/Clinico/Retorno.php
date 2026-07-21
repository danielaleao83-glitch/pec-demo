<?php

namespace App\Models\Clinico;

use App\Models\Atendimento\Atendimento;
use App\Models\Paciente\Paciente;
use App\Models\Sistema\BaseModel;
use App\Traits\HasSensitiveDataAudit;
use Illuminate\Database\Eloquent\SoftDeletes;

class Retorno extends BaseModel
{
    use HasSensitiveDataAudit, SoftDeletes;

    protected $table = 'retornos';

    protected $fillable = [
        'paciente_id',
        'atendimento_id_origem',
        'data_retorno',
        'motivo',
        'assinatura_medico',
    ];

    protected $casts = [
        'data_retorno' => 'datetime',
    ];

    // ---------------------------
    // RELACIONAMENTOS
    // ---------------------------

    public function paciente()
    {
        return $this->belongsTo(Paciente::class)->withTrashed();
    }

    public function atendimentoOrigem()
    {
        return $this->belongsTo(Atendimento::class, 'atendimento_id_origem')->withTrashed();
    }

    public function atendimentosRetorno()
    {
        return $this->hasMany(Atendimento::class, 'retorno_id');
    }
}