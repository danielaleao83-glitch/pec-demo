<?php

namespace App\Models\Procedimentos;

use App\Models\Atendimento\Atendimento;
use App\Models\Estabelecimentos\Unidade;
use App\Models\Paciente\Paciente;
use App\Models\Procedimentos\SigtapProcedimento;
use App\Models\Producao\ItemProducao;
use App\Models\Sistema\BaseModel;
use App\Traits\HasSensitiveDataAudit;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProcedimentoPaciente extends BaseModel
{
    use HasSensitiveDataAudit, SoftDeletes;

    protected $table = 'procedimentos_paciente';

    protected $fillable = [
        'paciente_id',
        'atendimento_id',
        'unidade_id',
        'sigtap_procedimento_id',
        'quantidade',
        'data_realizacao',
        'assinatura_medico',
    ];

    protected $guarded = [
        'id',
        'created_by',
        'updated_by',
        'deleted_at',
    ];

    protected $casts = [
        'quantidade' => 'integer',
        'data_realizacao' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'assinatura_medico' => 'string',
    ];

    // --------------------------------------------------------------------------
    // RELACIONAMENTOS
    // --------------------------------------------------------------------------
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

    public function sigtap()
    {
        return $this->belongsTo(SigtapProcedimento::class, 'sigtap_procedimento_id');
    }

    public function itensProducao()
    {
        return $this->hasMany(ItemProducao::class, 'procedimento_paciente_id');
    }

    // --------------------------------------------------------------------------
    // BOOT (AUDITORIA + PRODUÇÃO)
    // --------------------------------------------------------------------------
    protected static function booted(): void
    {
        static::creating(function ($model) {
            $model->validar();

            if (Auth::check()) {
                $model->created_by = Auth::id();
            }

            self::registrarAudit($model, 'create');
        });

        static::created(function ($model) {
            // Integração automática com produção (BPA)
            ItemProducao::create([
                'procedimento_paciente_id' => $model->id,
                'procedimento_id' => $model->sigtap_procedimento_id,
                'quantidade' => $model->quantidade,
                'data_execucao' => $model->data_realizacao,
                'atendimento_id' => $model->atendimento_id,
            ]);
        });

        static::updating(function ($model) {
            $model->validar();

            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }

            self::registrarAudit($model, 'update');
        });

        static::deleting(function ($model) {
            self::registrarAudit($model, 'delete');
        });
    }

    // --------------------------------------------------------------------------
    // AUDITORIA PADRÃO
    // --------------------------------------------------------------------------
    protected static function registrarAudit($model, $acao)
    {
        if (Auth::check()) {
            $userId = Auth::id();

            $model->{$acao.'_by'} = $userId;

            $model->registrarAcesso(
                $userId,
                $acao,
                'procedimentos_paciente',
                $model->id ?? null,
                $model->getOriginal(),
                $model->attributesToArray()
            );

            if ($model->assinatura_medico) {
                $model->registrarAcesso(
                    $userId,
                    $acao === 'update' ? 'assinatura_digital_update' : 'assinatura_digital',
                    'procedimentos_paciente',
                    $model->id ?? null,
                    $acao === 'update' ? $model->getOriginal('assinatura_medico') : null,
                    ['assinatura_medico' => $model->assinatura_medico]
                );
            }
        }
    }

    // --------------------------------------------------------------------------
    // VALIDAÇÃO FORTE
    // --------------------------------------------------------------------------
    protected function validar(): void
    {
        $validator = Validator::make($this->attributesToArray(), [
            'paciente_id' => 'required|integer|exists:pacientes,id',
            'atendimento_id' => 'required|integer|exists:atendimentos,id',
            'unidade_id' => 'required|integer|exists:unidades,id',
            'sigtap_procedimento_id' => 'required|integer|exists:sigtap_procedimentos,id',
            'quantidade' => 'required|integer|min:1',
            'data_realizacao' => 'required|date|before_or_equal:now',
            'assinatura_medico' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }
    }
}
