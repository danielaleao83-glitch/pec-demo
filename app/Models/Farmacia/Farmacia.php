<?php

namespace App\Models\Farmacia;

use App\Models\Atendimento\Atendimento;
use App\Models\Paciente\Paciente;
use App\Models\Sistema\BaseModel;
use App\Traits\HasSensitiveDataAudit;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class Farmacia extends BaseModel
{
    use HasSensitiveDataAudit, SoftDeletes;

    protected $table = 'farmacia';

    protected $fillable = [
        'paciente_id',
        'atendimento_id',
        'medicamento',
        'quantidade',
        'via',
        'frequencia',
        'duracao',
        'observacoes',
        'assinatura_medico',
    ];

    protected $casts = [
        'quantidade' => 'integer',
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
    // BOOT (AUDITORIA + VALIDAÇÃO)
    // -------------------------------------------
    protected static function booted(): void
    {
        static::creating(function ($model) {
            $model->validarCamposObrigatorios();

            if (Auth::check()) {
                $model->created_by = Auth::id();
            }

            self::registrarAudit($model, 'create');
        });

        static::updating(function ($model) {
            $model->validarCamposObrigatorios();

            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }

            self::registrarAudit($model, 'update');
        });

        static::deleting(function ($model) {
            self::registrarAudit($model, 'delete');
        });
    }

    protected static function registrarAudit($model, $acao)
    {
        if (Auth::check()) {

            $userId = Auth::id();

            $model->{$acao.'_by'} = $userId;

            $model->registrarAcesso(
                $userId,
                $acao,
                'farmacia',
                $model->id ?? null,
                $model->getOriginal(),
                $model->attributesToArray()
            );

            if ($model->assinatura_medico) {
                $model->registrarAcesso(
                    $userId,
                    'assinatura_digital',
                    'farmacia',
                    $model->id ?? null,
                    null,
                    ['assinatura_medico' => $model->assinatura_medico]
                );
            }
        }
    }

    // -------------------------------------------
    // VALIDAÇÃO
    // -------------------------------------------
    protected function validarCamposObrigatorios(): void
    {
        $validator = Validator::make($this->attributesToArray(), [
            'paciente_id' => 'required|integer|exists:pacientes,id',
            'atendimento_id' => 'required|integer|exists:atendimentos,id',
            'medicamento' => 'required|string|max:255',
            'quantidade' => 'required|integer|min:1',
            'via' => 'nullable|string|max:50',
            'frequencia' => 'nullable|string|max:50',
            'duracao' => 'nullable|string|max:50',
            'observacoes' => 'nullable|string|max:2000',
            'assinatura_medico' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }
    }

    // -------------------------------------------
    // SEGURANÇA
    // -------------------------------------------
    public function setObservacoesAttribute($value)
    {
        $this->attributes['observacoes'] = $value ? strip_tags($value) : null;
    }
}
