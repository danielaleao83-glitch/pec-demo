<?php

namespace App\Models\Exames;

use App\Models\Paciente\Paciente;
use App\Models\Sistema\BaseModel;
use App\Traits\HasSensitiveDataAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class ResultadoExame extends BaseModel
{
    use HasFactory, HasSensitiveDataAudit, SoftDeletes;

    protected $table = 'resultados_exames';

    protected $fillable = [
        'paciente_id',
        'atendimento_id',
        'tipo_exame',
        'resultado',
        'observacao',
        'data_exame',
    ];

    protected $guarded = [
        'id',
        'created_by',
        'updated_by',
        'deleted_at',
    ];

    protected $casts = [
        'tipo_exame' => 'string',
        'resultado' => 'string',
        'observacao' => 'string',
        'data_exame' => 'immutable_datetime',
        'created_at' => 'immutable_datetime',
        'updated_at' => 'immutable_datetime',
        'deleted_at' => 'immutable_datetime',
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
    // SCOPES AVANÇADOS
    // ------------------------------------------------------------------
    public function scopePorPaciente($query, int $pacienteId)
    {
        return $query->where('paciente_id', $pacienteId);
    }

    public function scopeDoAtendimento($query, int $atendimentoId)
    {
        return $query->where('atendimento_id', $atendimentoId);
    }

    public function scopePorPeriodo($query, string $inicio, string $fim)
    {
        return $query->whereBetween('data_exame', [$inicio, $fim]);
    }

    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo_exame', $tipo);
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
            if (Auth::check()) {
                $model->created_by = Auth::id();
            }
            $model->registrarAcesso(
                Auth::id(),
                'create',
                'resultado_exame',
                null,
                null,
                $model->attributesToArray()
            );
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
            $model->registrarAcesso(
                Auth::id(),
                'update',
                'resultado_exame',
                $model->id,
                $model->getOriginal(),
                $model->getDirty()
            );
        });

        static::deleting(function ($model) {
            $model->registrarAcesso(
                Auth::id(),
                'delete',
                'resultado_exame',
                $model->id,
                $model->attributesToArray(),
                null
            );
        });
    }
}
