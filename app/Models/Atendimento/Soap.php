<?php

namespace App\Models\Atendimento;

use App\Models\Paciente\Paciente;
use App\Models\Permissoes\User;
use App\Models\Sistema\BaseModel;
use App\Traits\HasSensitiveDataAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class Soap extends BaseModel
{
    use HasFactory, HasSensitiveDataAudit, SoftDeletes;

    protected $table = 'soaps';

    protected $fillable = [
        'atendimento_id',
        'paciente_id',
        'profissional_id',
        'tipo',
        'status',
        'subjetivo',
        'objetivo',
        'avaliacao',
        'plano',
        'observacoes',
    ];

    protected $guarded = [
        'id', 'created_by', 'updated_by', 'deleted_at',
    ];

    protected $casts = [
        'subjetivo' => 'string',
        'objetivo' => 'string',
        'avaliacao' => 'string',
        'plano' => 'string',
        'observacoes' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public const STATUS_RASCUNHO = 'rascunho';

    public const STATUS_FINALIZADO = 'finalizado';

    public const STATUS_CANCELADO = 'cancelado';

    protected const STATUS_VALIDOS = [
        self::STATUS_RASCUNHO,
        self::STATUS_FINALIZADO,
        self::STATUS_CANCELADO,
    ];

    public const TIPO_CLINICO = 'clinico';

    public const TIPO_ADMINISTRATIVO = 'administrativo';

    public const TIPO_OUTRO = 'outro';

    protected const TIPO_VALIDOS = [
        self::TIPO_CLINICO,
        self::TIPO_ADMINISTRATIVO,
        self::TIPO_OUTRO,
    ];

    // ------------------------------------------------------------------
    // RELACIONAMENTOS
    // ------------------------------------------------------------------
    public function atendimento()
    {
        return $this->belongsTo(Atendimento::class)->withTrashed();
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class)->withTrashed();
    }

    public function profissional()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function procedimentos()
    {
        return $this->belongsToMany(
            \App\Models\Procedimentos\SigtapProcedimento::class,
            'atendimento_procedimentos',
            'soap_id',
            'procedimento_id'
        )->withTimestamps();
    }

    // ------------------------------------------------------------------
    // SCOPES
    // ------------------------------------------------------------------
    public function scopeDoAtendimento($query, int $atendimentoId)
    {
        return $query->where('atendimento_id', $atendimentoId);
    }

    public function scopePorPaciente($query, int $pacienteId)
    {
        return $query->where('paciente_id', $pacienteId);
    }

    public function scopePorProfissional($query, int $profissionalId)
    {
        return $query->where('profissional_id', $profissionalId);
    }

    public function scopePorStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopeAtivos($query)
    {
        return $query->whereNull('deleted_at');
    }

    // ------------------------------------------------------------------
    // EVENTOS AUTOMÁTICOS COM AUDITORIA
    // ------------------------------------------------------------------
    protected static function booted(): void
    {
        static::creating(function ($model) {
            $model->validarCamposObrigatorios();

            if (Auth::check()) {
                $model->created_by = Auth::id();
                $model->profissional_id = $model->profissional_id ?? Auth::id();
            }

            $model->registrarAcesso(
                Auth::id(),
                'create',
                'soap',
                null,
                null,
                $model->attributesToArray()
            );
        });

        static::updating(function ($model) {
            $model->validarCamposObrigatorios();

            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }

            $model->registrarAcesso(
                Auth::id(),
                'update',
                'soap',
                $model->id,
                $model->getOriginal(),
                $model->getDirty()
            );
        });

        static::deleting(function ($model) {
            $model->registrarAcesso(
                Auth::id(),
                'delete',
                'soap',
                $model->id,
                $model->attributesToArray(),
                null
            );
        });
    }

    // ------------------------------------------------------------------
    // VALIDAÇÃO FORTE
    // ------------------------------------------------------------------
    protected function validarCamposObrigatorios(): void
    {
        $validator = Validator::make($this->attributesToArray(), [
            'atendimento_id' => 'required|integer|exists:atendimentos,id',
            'paciente_id' => 'required|integer|exists:pacientes,id',
            'profissional_id' => 'required|integer|exists:users,id',
            'tipo' => 'required|string|in:'.implode(',', self::TIPO_VALIDOS),
            'status' => 'required|string|in:'.implode(',', self::STATUS_VALIDOS),
            'subjetivo' => 'nullable|string|max:2000',
            'objetivo' => 'nullable|string|max:2000',
            'avaliacao' => 'nullable|string|max:2000',
            'plano' => 'nullable|string|max:2000',
            'observacoes' => 'nullable|string|max:4000',
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
}
