<?php

namespace App\Models\Assistencial\Anexos;

use App\Models\Clinico\PreNatal;
use App\Models\Estabelecimentos\Unidade;
use App\Models\Paciente\Paciente;
use App\Models\Sistema\BaseModel;
use App\Traits\HasSensitiveDataAudit;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PreNatalVacina extends BaseModel
{
    use HasSensitiveDataAudit, SoftDeletes;

    protected $table = 'pre_natal_vacinas';

    protected $fillable = [
        'uuid',
        'paciente_id',
        'pre_natal_id',
        'vacina_id',
        'data_aplicacao',
        'dose',
        'lote',
        'unidade_id',
        'observacoes',
    ];

    protected $casts = [
        'data_aplicacao' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /*
    |-----------------------------------------
    | UUID AUTOMÁTICO (ESSENCIAL EM PRODUÇÃO)
    |-----------------------------------------
    */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {

            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }

            $model->validarCamposObrigatorios();

            if (Auth::check()) {
                $model->created_by = Auth::id();
            }
        });
    }

    /*
    |-----------------------------------------
    | RELACIONAMENTOS
    |-----------------------------------------
    */

    public function paciente()
    {
        return $this->belongsTo(Paciente::class)->withTrashed();
    }

    public function preNatal()
    {
        return $this->belongsTo(PreNatal::class)->withTrashed();
    }

    public function unidade()
    {
        return $this->belongsTo(Unidade::class)->withTrashed();
    }

    /*
    |-----------------------------------------
    | AUDITORIA
    |-----------------------------------------
    */

    protected static function registrarAudit($model, string $acao): void
    {
        if (!Auth::check()) return;

        $userId = Auth::id();

        $model->{$acao . '_by'} = $userId;

        $model->registrarAcesso(
            $userId,
            $acao,
            'pre_natal_vacinas',
            $model->id ?? null,
            $model->getOriginal(),
            $model->attributesToArray()
        );
    }

    /*
    |-----------------------------------------
    | VALIDAÇÃO SEGURA
    |-----------------------------------------
    */

    protected function validarCamposObrigatorios(): void
    {
        $validator = Validator::make($this->attributesToArray(), [
            'paciente_id'    => 'required|integer|exists:pacientes,id',
            'pre_natal_id'   => 'required|integer|exists:pre_natal,id',
            'vacina_id'      => 'required|integer',
            'data_aplicacao' => 'required|date|before_or_equal:now',
            'dose'           => 'required|string|max:100',
            'lote'           => 'nullable|string|max:50',
            'unidade_id'     => 'required|integer|exists:unidades,id',
            'observacoes'    => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }
    }

    /*
    |-----------------------------------------
    | SEGURANÇA
    |-----------------------------------------
    */

    public function setObservacoesAttribute($value)
    {
        $this->attributes['observacoes'] = strip_tags($value);
    }
}