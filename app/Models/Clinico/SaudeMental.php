<?php

namespace App\Models\Clinico;

use App\Models\Paciente\Paciente;
use App\Models\Sistema\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaudeMental extends BaseModel
{
    use SoftDeletes;

    protected $table = 'saude_mental';

    protected $fillable = [
        'paciente_id',
        'condicao',
        'observacoes',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class)->withTrashed();
    }
}
