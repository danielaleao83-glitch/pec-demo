<?php

namespace App\Models\RegistroMultiprofissional;

use App\Models\Paciente\Paciente;
use App\Models\Sistema\BaseModel;
use App\Traits\HasSensitiveDataAudit;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CrescimentoInfantil extends BaseModel
{
    use HasSensitiveDataAudit, SoftDeletes;

    protected $table = 'crescimento_infantil';

    protected $fillable = [
        'paciente_id',
        'idade_meses',
        'peso',
        'altura',
        'imc',
        'percentil',
    ];

    protected $casts = [
        'peso' => 'float',
        'altura' => 'float',
        'imc' => 'float',
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

    // -------------------------------------------
    // EVENTOS (AUDITORIA + VALIDAÇÃO)
    // -------------------------------------------
    protected static function booted(): void
    {
        static::creating(function ($model) {
            $model->validarCamposObrigatorios();
            self::registrarAudit($model, 'create');
        });

        static::updating(function ($model) {
            $model->validarCamposObrigatorios();
            self::registrarAudit($model, 'update');
        });

        static::deleting(function ($model) {
            self::registrarAudit($model, 'delete');
        });
    }

    // -------------------------------------------
    // REGISTRO DE AUDITORIA
    // -------------------------------------------
    protected static function registrarAudit($model, $acao)
    {
        if (Auth::check()) {
            $userId = Auth::id();

            $model->registrarAcesso(
                $userId,
                $acao,
                'crescimento_infantil',
                $model->id ?? null,
                $model->getOriginal(),
                $model->attributesToArray()
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
            'idade_meses' => 'required|integer|min:0',
            'peso' => 'nullable|numeric|min:0',
            'altura' => 'nullable|numeric|min:0',
            'imc' => 'nullable|numeric|min:0',
            'percentil' => 'nullable|string|max:10',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }
    }
}
