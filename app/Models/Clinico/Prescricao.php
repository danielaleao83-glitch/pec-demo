<?php

namespace App\Models\Clinico;

use App\Models\Atendimento\Atendimento;
use App\Models\Estabelecimentos\Unidade;
use App\Models\Paciente\Paciente;
use App\Models\Sistema\BaseModel;
use App\Traits\HasSensitiveDataAudit;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class Prescricao extends BaseModel
{
    use HasSensitiveDataAudit, SoftDeletes;

    protected $table = 'prescricoes';

    protected $fillable = [
        'uuid',
        'paciente_id',
        'atendimento_id',
        'unidade_id',
        'medicamento',
        'dose',
        'via',
        'frequencia',
        'duracao',
        'assinatura_medico',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /*
    |----------------------------------------
    | UUID (PADRÃO GLOBAL SUS)
    |----------------------------------------
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
    |----------------------------------------
    | RELACIONAMENTOS
    |----------------------------------------
    */

    public function paciente()
    {
        return $this->belongsTo(Paciente::class)->withTrashed();
    }

    public function atendimento()
    {
        return $this->belongsTo(Atendimento::class)->withTrashed();
    }

    public function unidade()
    {
        return $this->belongsTo(Unidade::class)->withTrashed();
    }

    /*
    |----------------------------------------
    | AUDITORIA
    |----------------------------------------
    */

    protected static function registrarAudit($model, string $acao): void
    {
        if (!Auth::check()) return;

        $userId = Auth::id();

        $model->{$acao . '_by'} = $userId;

        $model->registrarAcesso(
            $userId,
            $acao,
            'prescricoes',
            $model->id ?? null,
            $model->getOriginal(),
            $model->attributesToArray()
        );

        if ($model->assinatura_medico) {
            $model->registrarAcesso(
                $userId,
                'assinatura_digital',
                'prescricoes',
                $model->id ?? null,
                null,
                ['assinatura_medico' => $model->assinatura_medico]
            );
        }
    }

    /*
    |----------------------------------------
    | VALIDAÇÃO SEGURA
    |----------------------------------------
    */

    protected function validarCamposObrigatorios(): void
    {
        $validator = Validator::make($this->attributesToArray(), [
            'paciente_id' => 'required|integer|exists:pacientes,id',
            'atendimento_id' => 'required|integer|exists:atendimentos,id',
            'unidade_id' => 'required|integer|exists:unidades,id',

            'medicamento' => 'required|string|max:255',
            'dose' => 'required|string|max:100',
            'via' => 'required|string|max:100',
            'frequencia' => 'required|string|max:100',
            'duracao' => 'required|string|max:100',

            'assinatura_medico' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }
    }

    /*
    |----------------------------------------
    | SEGURANÇA
    |----------------------------------------
    */

    public function setMedicamentoAttribute($value)
    {
        $this->attributes['medicamento'] = strip_tags($value);
    }

    public function setViaAttribute($value)
    {
        $this->attributes['via'] = strip_tags($value);
    }

    public function setFrequenciaAttribute($value)
    {
        $this->attributes['frequencia'] = strip_tags($value);
    }

    public function setDuracaoAttribute($value)
    {
        $this->attributes['duracao'] = strip_tags($value);
    }
}