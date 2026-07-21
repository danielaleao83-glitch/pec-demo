<?php

namespace App\Models\Clinico;

use App\Models\Atendimento\Atendimento;
use App\Models\Paciente\Paciente;
use App\Models\Sistema\BaseModel;
use App\Traits\HasSensitiveDataAudit;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DoencaCronica extends BaseModel
{
    use HasSensitiveDataAudit, SoftDeletes;

    protected $table = 'doencas_cronicas';

    protected $fillable = [
        'paciente_id',
        'atendimento_id',
        'tipo_doenca',
        'diagnostico_data',
        'observacoes',
        'assinatura_medico',
    ];

    protected $casts = [
        'diagnostico_data' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class)->withTrashed();
    }

    public function atendimento()
    {
        return $this->belongsTo(Atendimento::class)->withTrashed();
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            $model->validarCamposObrigatorios();

            if (Auth::check()) {
                $model->created_by = Auth::id();
            }

            $model->registrarAcesso(
                Auth::id(),
                'create',
                'doenca_cronica',
                null,
                null,
                $model->attributesToArray()
            );
        });

        static::updating(function ($model) {
            $model->validarCamposObrigatorios();

            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }

            $model->registrarAcesso(
                Auth::id(),
                'update',
                'doenca_cronica',
                $model->id,
                $model->getOriginal(),
                $model->getDirty()
            );
        });

        static::deleting(function ($model) {
            $model->registrarAcesso(
                Auth::id(),
                'delete',
                'doenca_cronica',
                $model->id,
                $model->attributesToArray(),
                null
            );
        });
    }

    protected function validarCamposObrigatorios(): void
    {
        $validator = Validator::make($this->attributesToArray(), [
            'paciente_id' => 'required|integer|exists:pacientes,id',
            'atendimento_id' => 'required|integer|exists:atendimentos,id',
            'tipo_doenca' => 'required|string|max:255',
            'diagnostico_data' => 'required|date|before_or_equal:now',
            'observacoes' => 'nullable|string|max:2000',
            'assinatura_medico' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }
    }
}
