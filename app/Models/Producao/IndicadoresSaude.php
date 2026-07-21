<?php

namespace App\Models\Producao;

use App\Models\Sistema\BaseModel;
use App\Traits\HasSensitiveDataAudit;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class IndicadoresSaude extends BaseModel
{
    use HasSensitiveDataAudit, SoftDeletes;

    protected $table = 'indicadores_saude';

    protected $fillable = [
        'nome_indicador',
        'descricao',
        'valor',
        'periodo_inicio',
        'periodo_fim',
        'created_by',
        'updated_by',
    ];

    protected $guarded = [
        'id',
        'deleted_at',
    ];

    protected $casts = [
        'periodo_inicio' => 'datetime',
        'periodo_fim' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'valor' => 'float',
    ];

    // ------------------------------------------------------------------
    // EVENTOS (AUDITORIA)
    // ------------------------------------------------------------------
    protected static function booted(): void
    {
        static::creating(function ($model) {

            $model->validarCamposObrigatorios();

            $userId = Auth::id();

            if ($userId) {
                $model->created_by = $userId;
            }

            if ($userId) {
                $model->registrarAcesso(
                    $userId,
                    'create',
                    'indicadores_saude',
                    null,
                    null,
                    $model->attributesToArray()
                );
            }
        });

        static::updating(function ($model) {

            $model->validarCamposObrigatorios();

            $userId = Auth::id();

            if ($userId) {
                $model->updated_by = $userId;

                $model->registrarAcesso(
                    $userId,
                    'update',
                    'indicadores_saude',
                    $model->id,
                    $model->getOriginal(),
                    $model->getDirty()
                );
            }
        });

        static::deleting(function ($model) {

            $userId = Auth::id();

            if ($userId) {
                $model->registrarAcesso(
                    $userId,
                    'delete',
                    'indicadores_saude',
                    $model->id,
                    $model->attributesToArray(),
                    null
                );
            }
        });
    }

    // ------------------------------------------------------------------
    // VALIDAÇÃO
    // ------------------------------------------------------------------
    protected function validarCamposObrigatorios(): void
    {
        $validator = Validator::make($this->attributesToArray(), [
            'nome_indicador' => 'required|string|max:255',
            'descricao' => 'nullable|string|max:2000',
            'valor' => 'required|numeric',
            'periodo_inicio' => 'required|date|before_or_equal:now',
            'periodo_fim' => 'required|date|after_or_equal:periodo_inicio',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }
    }
}
