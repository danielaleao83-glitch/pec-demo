<?php

namespace App\Models\Clinico;

use App\Models\Atendimento\Atendimento;
use App\Models\Paciente\Paciente;
use App\Models\Sistema\BaseModel;
use App\Traits\HasSensitiveDataAudit;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PlanejamentoFamiliar extends BaseModel
{
    use HasSensitiveDataAudit, SoftDeletes;

    protected $table = 'planejamento_familiar';

    protected $fillable = [
        'paciente_id',
        'atendimento_id',
        'metodo_contraceptivo',
        'observacoes',
        'assinatura_medico',
    ];

    protected $guarded = [
        'id',
        'created_by',
        'updated_by',
        'deleted_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'assinatura_medico' => 'string',
    ];

    // ------------------------------------------------------------------
    // RELACIONAMENTOS
    // ------------------------------------------------------------------
    public function paciente()
    {
        return $this->belongsTo(Paciente::class)->withTrashed();
    }

    public function atendimento()
    {
        return $this->belongsTo(Atendimento::class)->withTrashed();
    }

    // ------------------------------------------------------------------
    // EVENTOS (AUDITORIA + VALIDAÇÃO)
    // ------------------------------------------------------------------
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

            $model->{$acao.'_by'} = $userId; // 🔥 PADRÃO DO SISTEMA

            $model->registrarAcesso(
                $userId,
                $acao,
                'planejamento_familiar',
                $model->id ?? null,
                $model->getOriginal(),
                $model->attributesToArray()
            );

            if ($model->assinatura_medico) {
                $model->registrarAcesso(
                    $userId,
                    'assinatura_digital',
                    'planejamento_familiar',
                    $model->id ?? null,
                    null,
                    ['assinatura_medico' => $model->assinatura_medico]
                );
            }
        }
    }

    // ------------------------------------------------------------------
    // VALIDAÇÃO
    // ------------------------------------------------------------------
    protected function validarCamposObrigatorios(): void
    {
        $validator = Validator::make($this->attributesToArray(), [
            'paciente_id' => 'required|integer|exists:pacientes,id',
            'atendimento_id' => 'required|integer|exists:atendimentos,id',
            'metodo_contraceptivo' => 'required|string|max:255',
            'observacoes' => 'nullable|string|max:2000',
            'assinatura_medico' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }
    }

    // 🔐 SEGURANÇA EXTRA (PADRÃO DO SISTEMA)
    public function setObservacoesAttribute($value)
    {
        $this->attributes['observacoes'] = strip_tags($value);
    }
}
