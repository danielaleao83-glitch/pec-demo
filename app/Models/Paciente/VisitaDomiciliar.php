<?php

namespace App\Models\Paciente;

use App\Models\Atendimento\Atendimento;
use App\Models\Paciente\Paciente;
use App\Models\Permissoes\User;
use App\Models\Sistema\BaseModel;
use App\Traits\HasSensitiveDataAudit;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class VisitaDomiciliar extends BaseModel
{
    use HasSensitiveDataAudit, SoftDeletes;

    protected $table = 'visitas_domiciliares';

    protected $fillable = [
        'paciente_id',
        'atendimento_id',
        'profissional_id',
        'data_visita',
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
        'data_visita' => 'datetime',
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

    public function profissional()
    {
        return $this->belongsTo(User::class, 'profissional_id')->withTrashed();
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
            if (Auth::check()) {
                self::registrarAudit($model, 'delete');
            }
        });
    }

    protected static function registrarAudit($model, $acao)
    {
        if (! Auth::check()) {
            return;
        }

        $userId = Auth::id();

        $model->registrarAcesso(
            $userId,
            $acao,
            'visitas_domiciliares',
            $model->id ?? null,
            $model->getOriginal(),
            $model->attributesToArray()
        );

        // Auditoria assinatura digital
        if ($model->assinatura_medico) {
            $model->registrarAcesso(
                $userId,
                'assinatura_digital',
                'visitas_domiciliares',
                $model->id ?? null,
                null,
                ['assinatura_medico' => $model->assinatura_medico]
            );
        }
    }

    // -------------------------------------------
    // VALIDAÇÃO FORTE
    // -------------------------------------------
    protected function validarCamposObrigatorios(): void
    {
        $validator = Validator::make($this->attributesToArray(), [
            'paciente_id' => 'required|integer|exists:pacientes,id',
            'atendimento_id' => 'required|integer|exists:atendimentos,id',
            'profissional_id' => 'required|integer|exists:users,id',
            'data_visita' => 'required|date|before_or_equal:now',
            'observacoes' => 'nullable|string|max:2000',
            'assinatura_medico' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }
    }

    // -------------------------------------------
    // SANITIZAÇÃO DE CAMPOS STRING
    // -------------------------------------------
    public function setObservacoesAttribute($value)
    {
        $this->attributes['observacoes'] = $value ? strip_tags($value) : null;
    }
}
