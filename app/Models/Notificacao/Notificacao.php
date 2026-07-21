<?php

namespace App\Models\Notificacao;

use App\Models\Atendimento\Atendimento;
use App\Models\Paciente\Paciente;
use App\Models\Sistema\BaseModel;
use App\Traits\HasSensitiveDataAudit;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class Notificacao extends BaseModel
{
    use HasSensitiveDataAudit, SoftDeletes;

    protected $table = 'notificacoes';

    protected $fillable = [
        'paciente_id',
        'atendimento_id',
        'tipo',
        'descricao',
        'observacoes',
        'assinatura_medico',
        'created_by',
        'updated_by',
    ];

    protected $guarded = [
        'id',
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
    // EVENTOS (AUDITORIA)
    // ------------------------------------------------------------------
    protected static function booted(): void
    {
        static::creating(function ($model) {

            $model->validarCamposObrigatorios();

            if (Auth::check()) {
                $model->created_by = Auth::id();
            }

            static::registrarAudit($model, 'create');
        });

        static::updating(function ($model) {

            $model->validarCamposObrigatorios();

            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }

            static::registrarAudit($model, 'update');
        });

        static::deleting(function ($model) {
            static::registrarAudit($model, 'delete');
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
            'notificacoes',
            $model->id ?? null,
            $acao === 'create' ? null : $model->getOriginal(),
            $acao === 'update' ? $model->getDirty() : $model->attributesToArray()
        );

        // 🔐 Auditoria de assinatura digital (somente quando altera)
        if ($model->isDirty('assinatura_medico')) {
            $model->registrarAcesso(
                $userId,
                'assinatura_digital',
                'notificacoes',
                $model->id ?? null,
                $model->getOriginal('assinatura_medico'),
                $model->assinatura_medico
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
            'tipo' => 'required|string|max:255',
            'descricao' => 'required|string|max:2000',
            'observacoes' => 'nullable|string|max:2000',
            'assinatura_medico' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }
    }
}
