<?php

namespace App\Models\Procedimentos;

use App\Models\Paciente\Paciente;
use App\Models\Permissoes\User;
use App\Models\Sistema\BaseModel;
use App\Traits\HasSensitiveDataAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Exame extends BaseModel
{
    use HasFactory, HasSensitiveDataAudit, SoftDeletes;

    protected $table = 'exames';

    protected $fillable = [
        'atendimento_id',
        'paciente_id',
        'profissional_id',
        'nome_exame',
        'descricao',
        'resultado',
        'tipo',         // laboratorial, imagem, clínico, etc.
        'prioridade',   // baixa, média, alta, emergência
        'status',
        'observacoes',
    ];

    protected $guarded = [
        'id',
        'created_by',
        'updated_by',
        'deleted_at',
    ];

    // -------------------------------------------
    // Status possíveis
    // -------------------------------------------
    public const STATUS_SOLICITADO = 'solicitado';

    public const STATUS_EM_ANALISE = 'em_analise';

    public const STATUS_CONCLUIDO = 'concluido';

    public const STATUS_CANCELADO = 'cancelado';

    protected const STATUS_VALIDOS = [
        self::STATUS_SOLICITADO,
        self::STATUS_EM_ANALISE,
        self::STATUS_CONCLUIDO,
        self::STATUS_CANCELADO,
    ];

    // -------------------------------------------
    // Casts padronizados
    // -------------------------------------------
    protected $casts = [
        'tipo' => 'string',
        'prioridade' => 'string',
        'status' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // -------------------------------------------
    // Eventos automáticos (Auditoria Governamental)
    // -------------------------------------------
    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (! in_array($model->status, self::STATUS_VALIDOS)) {
                $model->status = self::STATUS_SOLICITADO;
            }
            if (Auth::check()) {
                $model->created_by = Auth::id();
            }
            $model->registrarAcesso(Auth::id(), 'create', 'exame', null, null, $model->attributesToArray());
        });

        static::updating(function ($model) {
            if (! in_array($model->status, self::STATUS_VALIDOS)) {
                throw new \InvalidArgumentException('Status de exame inválido.');
            }
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
            $model->registrarAcesso(Auth::id(), 'update', 'exame', $model->id, $model->getOriginal(), $model->getDirty());
        });

        static::deleting(function ($model) {
            $model->registrarAcesso(Auth::id(), 'delete', 'exame', $model->id, $model->attributesToArray(), null);
        });
    }

    // -------------------------------------------
    // Relacionamentos
    // -------------------------------------------
    public function atendimento()
    {
        return $this->belongsTo(Atendimento::class, 'atendimento_id')->withTrashed();
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id')->withTrashed();
    }

    public function profissional()
    {
        return $this->belongsTo(User::class, 'profissional_id')->withTrashed();
    }

    // -------------------------------------------
    // Scopes avançados
    // -------------------------------------------
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

    public function scopePorPeriodo($query, string $inicio, string $fim)
    {
        return $query->whereBetween('created_at', [$inicio, $fim]);
    }

    public function scopeSolicitados($query)
    {
        return $query->where('status', self::STATUS_SOLICITADO);
    }
}
