<?php

namespace App\Models\Atendimento;

use App\Models\Atendimento\Atendimento;
use App\Models\Paciente\Paciente;
use App\Models\Sistema\BaseModel;
use App\Traits\HasSensitiveDataAudit;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SinalVital extends BaseModel
{
    use HasSensitiveDataAudit, SoftDeletes;

    protected $table = 'sinais_vitais';

    protected $fillable = [
        'paciente_id',
        'atendimento_id',
        'data_registro',
        'peso',
        'altura',
        'pa',
        'fc',
        'temperatura',
        'imc',
    ];

    protected $guarded = [
        'id',
        'created_by',
        'updated_by',
        'deleted_at',
    ];

    protected $casts = [
        'data_registro' => 'datetime',
        'peso' => 'float',
        'altura' => 'float',
        'temperatura' => 'float',
        'imc' => 'float',
        'fc' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
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
                self::registrarAudit($model, 'create');
            }
        });

        static::updating(function ($model) {
            $model->validarCamposObrigatorios();

            if (Auth::check()) {
                $model->updated_by = Auth::id();
                self::registrarAudit($model, 'update');
            }
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
            'sinais_vitais',
            $model->id ?? null,
            $model->getOriginal(),
            $model->attributesToArray()
        );
    }

    // -------------------------------------------
    // VALIDAÇÃO FORTE
    // -------------------------------------------
    protected function validarCamposObrigatorios(): void
    {
        $validator = Validator::make($this->attributesToArray(), [
            'paciente_id' => 'required|integer|exists:pacientes,id',
            'atendimento_id' => 'required|integer|exists:atendimentos,id',
            'data_registro' => 'required|date|before_or_equal:now',

            'peso' => 'nullable|numeric|min:0|max:500',
            'altura' => 'nullable|numeric|min:0|max:3',
            'pa' => 'nullable|string|max:20',
            'fc' => 'nullable|integer|min:0|max:250',
            'temperatura' => 'nullable|numeric|min:30|max:45',
            'imc' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException(
                $validator->errors()->first()
            );
        }
    }

    // -------------------------------------------
    // SANITIZAÇÃO
    // -------------------------------------------
    public function setPaAttribute($value)
    {
        $this->attributes['pa'] = $value ? strip_tags($value) : null;
    }
}
