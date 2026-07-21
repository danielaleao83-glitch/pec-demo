<?php

namespace App\Models\Clinico;

use App\Models\Atendimento\Atendimento;
use App\Models\Paciente\Paciente;
use App\Models\Sistema\BaseModel;
use App\Traits\HasSensitiveDataAudit;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PreNatal extends BaseModel
{
    use HasSensitiveDataAudit, SoftDeletes;

    protected $table = 'pre_natal';

    protected $fillable = [
        'paciente_id',
        'atendimento_id',
        'gestacao',
        'idade_gestacional_semanas',
        'riscos',
        'observacoes',
        'vacinas_aplicadas',
        'created_by',
        'updated_by',
    ];

    protected $guarded = [
        'id',
        'deleted_at',
    ];

    protected $casts = [
        'gestacao' => 'string',
        'idade_gestacional_semanas' => 'integer',
        'riscos' => 'string',
        'observacoes' => 'string',
        'vacinas_aplicadas' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
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
    // EVENTOS (PADRÃO UNIFICADO)
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
            $model->registrarAcesso(
                Auth::id(),
                $acao,
                'pre_natal',
                $model->id ?? null,
                $model->getOriginal(),
                $model->attributesToArray()
            );
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
            'gestacao' => 'required|string|max:255',
            'idade_gestacional_semanas' => 'required|integer|min:0|max:44',
            'riscos' => 'nullable|string|max:2000',
            'observacoes' => 'nullable|string|max:2000',
            'vacinas_aplicadas' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }
    }

    // ------------------------------------------------------------------
    // SEGURANÇA EXTRA
    // ------------------------------------------------------------------
    public function setObservacoesAttribute($value)
    {
        $this->attributes['observacoes'] = strip_tags($value);
    }

    public function setRiscosAttribute($value)
    {
        $this->attributes['riscos'] = strip_tags($value);
    }
}
