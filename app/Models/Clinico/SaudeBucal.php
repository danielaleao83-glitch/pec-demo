<?php

namespace App\Models\Clinico;

use App\Models\Atendimento\Atendimento;
use App\Models\Paciente\Paciente;
use App\Models\Sistema\BaseModel;
use App\Traits\HasSensitiveDataAudit;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SaudeBucal extends BaseModel
{
    use HasSensitiveDataAudit, SoftDeletes;

    protected $table = 'saude_bucal';

    protected $fillable = [
        'paciente_id',
        'atendimento_id',
        'condicao_dentaria',
        'observacoes',
        'assinatura_medico',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'assinatura_medico' => 'string',
    ];

    // -------------------------------------------
    // RELACIONAMENTOS
    // -------------------------------------------
    public function paciente()
    {
        return $this->belongsTo(Paciente::class)->withTrashed();
    }

    public function atendimento()
    {
        return $this->belongsTo(Atendimento::class)->withTrashed();
    }

    // -------------------------------------------
    // EVENTOS (AUDITORIA + VALIDAÇÃO)
    // -------------------------------------------
    protected static function booted(): void
    {
        static::creating(function ($model) {

            $model->validarCamposObrigatorios();

            if (Auth::check()) {
                $model->created_by = Auth::id();

                $model->registrarAcesso(
                    Auth::id(),
                    'create',
                    'saude_bucal',
                    null,
                    null,
                    $model->attributesToArray()
                );
            }

            if ($model->isDirty('assinatura_medico') && Auth::check()) {
                $model->registrarAcesso(
                    Auth::id(),
                    'assinatura_digital',
                    'saude_bucal',
                    null,
                    null,
                    ['assinatura_medico' => $model->assinatura_medico]
                );
            }
        });

        static::updating(function ($model) {

            $model->validarCamposObrigatorios();

            if (Auth::check()) {
                $model->updated_by = Auth::id();

                $model->registrarAcesso(
                    Auth::id(),
                    'update',
                    'saude_bucal',
                    $model->id,
                    $model->getOriginal(),
                    $model->getDirty()
                );
            }

            if ($model->isDirty('assinatura_medico') && Auth::check()) {
                $model->registrarAcesso(
                    Auth::id(),
                    'assinatura_medico_update',
                    'saude_bucal',
                    $model->id,
                    $model->getOriginal('assinatura_medico'),
                    $model->assinatura_medico
                );
            }
        });

        static::deleting(function ($model) {

            if (Auth::check()) {
                $model->registrarAcesso(
                    Auth::id(),
                    'delete',
                    'saude_bucal',
                    $model->id,
                    $model->attributesToArray(),
                    null
                );
            }
        });
    }

    // -------------------------------------------
    // VALIDAÇÃO
    // -------------------------------------------
    protected function validarCamposObrigatorios(): void
    {
        $validator = Validator::make($this->attributesToArray(), [
            'paciente_id' => 'required|integer|exists:pacientes,id',
            'atendimento_id' => 'required|integer|exists:atendimentos,id',
            'condicao_dentaria' => 'required|string|max:1000',
            'observacoes' => 'nullable|string|max:2000',
            'assinatura_medico' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }
    }
}
