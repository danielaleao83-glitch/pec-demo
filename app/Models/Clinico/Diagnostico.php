<?php

namespace App\Models\Clinico;

use App\Models\Atendimento\Atendimento;
use App\Models\Paciente\Paciente;
use App\Models\Sistema\BaseModel;
use App\Traits\HasSensitiveDataAudit;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Diagnostico extends BaseModel
{
    use HasSensitiveDataAudit, SoftDeletes;

    protected $table = 'diagnosticos';

    protected $fillable = [
        'paciente_id',
        'atendimento_id',
        'cid10',
        'descricao',
        'tipo',
        'assinatura_medico',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'deleted_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected $hidden = [
        'deleted_at',
    ];

    // --------------------------------------------------------------------------
    // CONSTANTES
    // --------------------------------------------------------------------------

    public const TIPO_PRINCIPAL = 'principal';

    public const TIPO_SECUNDARIO = 'secundario';

    public const TIPOS_VALIDOS = [
        self::TIPO_PRINCIPAL,
        self::TIPO_SECUNDARIO,
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
        return $this->belongsTo(Atendimento::class);
    }

    public function registroMultiprofissional()
    {
        return $this->morphOne(
            \App\Models\RegistroMultiprofissional\RegistroMultiprofissional::class,
            'registravel'
        );
    }

    // --------------------------------------------------------------------------
    // SCOPES
    // --------------------------------------------------------------------------

    public function scopePrincipal($query)
    {
        return $query->where('tipo', self::TIPO_PRINCIPAL);
    }

    public function scopeSecundario($query)
    {
        return $query->where('tipo', self::TIPO_SECUNDARIO);
    }

    public function scopePorPaciente($query, $pacienteId)
    {
        return $query->where('paciente_id', $pacienteId);
    }

    // --------------------------------------------------------------------------
    // EVENTOS (AUDITORIA + AUTOMAÇÃO)
    // --------------------------------------------------------------------------

    protected static function booted()
    {
        // ---------------- CREATE ----------------
        static::creating(function ($model) {

            if (Auth::check()) {
                $model->created_by = Auth::id();
            }

            // 🔒 Validação forte
            if (! in_array($model->tipo, self::TIPOS_VALIDOS)) {
                throw new \InvalidArgumentException('Tipo de diagnóstico inválido.');
            }

            // 🔐 Auditoria assinatura digital
            if ($model->assinatura_medico) {
                $model->registrarAcesso(
                    Auth::id(),
                    'assinatura_digital',
                    'diagnostico',
                    null,
                    null,
                    ['assinatura_medico' => $model->assinatura_medico]
                );
            }
        });

        // ---------------- CREATED ----------------
        static::created(function ($model) {

            \App\Models\RegistroMultiprofissional\RegistroMultiprofissional::create([
                'atendimento_id' => $model->atendimento_id,
                'profissional_id' => Auth::id() ?? $model->created_by,
                'tipo_profissional' => 'medico',
                'tipo_registro' => 'diagnostico',
                'registravel_id' => $model->id,
                'registravel_type' => self::class,
                'ip' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
            ]);
        });

        // ---------------- UPDATE ----------------
        static::updating(function ($model) {

            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }

            if ($model->isDirty('assinatura_medico')) {
                $model->registrarAcesso(
                    Auth::id(),
                    'assinatura_digital_update',
                    'diagnostico',
                    $model->id,
                    $model->getOriginal('assinatura_medico'),
                    $model->assinatura_medico
                );
            }
        });

        // ---------------- DELETE ----------------
        static::deleting(function ($model) {

            $model->loadMissing('registroMultiprofissional');

            if ($model->registroMultiprofissional) {
                $model->registroMultiprofissional->delete();
            }
        });
    }
}
