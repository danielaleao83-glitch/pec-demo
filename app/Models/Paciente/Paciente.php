<?php

namespace App\Models\Paciente;

use App\Traits\HasSensitiveDataAudit;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Paciente extends Model
{
    use HasFactory, HasSensitiveDataAudit, SoftDeletes;

    protected $table = 'pacientes';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'uuid',
        'nome',
        'cpf',
        'cpf_hash',
        'cns',
        'cns_hash',
        'data_nascimento',
        'sexo',
        'telefone',
        'email',
        'endereco',
        'nome_mae',
        'municipio',
        'prioridade',
        'ativo',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        'cpf',
        'cpf_hash',
        'cns',
        'cns_hash',
        'deleted_at',
    ];

    protected $casts = [
        'data_nascimento' => 'date',
        'ativo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // --------------------------------------------------------------------------
    // BOOT - AUDITORIA SEGURA (WEB + CLI + JOB)
    // --------------------------------------------------------------------------
    protected static function booted()
    {
        static::creating(function ($model) {

            $userId = Auth::id() ?? 0;

            // UUID interno (não PK)
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }

            $model->created_by = $userId;

            try {
                if (method_exists($model, 'registrarAcesso')) {
                    $model->registrarAcesso(
                        $userId,
                        'create',
                        'paciente',
                        null,
                        null,
                        $model->attributesToArray()
                    );
                }
            } catch (\Throwable $e) {
                Log::error('Erro auditoria create paciente', [
                    'erro' => $e->getMessage(),
                ]);
            }
        });

        static::updating(function ($model) {

            $userId = Auth::id() ?? 0;

            $model->updated_by = $userId;

            try {
                if (method_exists($model, 'registrarAcesso')) {
                    $model->registrarAcesso(
                        $userId,
                        'update',
                        'paciente',
                        $model->id,
                        $model->getOriginal(),
                        $model->getDirty()
                    );
                }
            } catch (\Throwable $e) {
                Log::error('Erro auditoria update paciente', [
                    'erro' => $e->getMessage(),
                ]);
            }
        });

        static::deleting(function ($model) {

            $userId = Auth::id() ?? 0;

            try {
                if (method_exists($model, 'registrarAcesso')) {
                    $model->registrarAcesso(
                        $userId,
                        'delete',
                        'paciente',
                        $model->id,
                        $model->attributesToArray(),
                        null
                    );
                }
            } catch (\Throwable $e) {
                Log::error('Erro auditoria delete paciente', [
                    'erro' => $e->getMessage(),
                ]);
            }
        });
    }

    // --------------------------------------------------------------------------
    // NORMALIZAÇÃO
    // --------------------------------------------------------------------------
    protected function nome(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => $value ? trim(preg_replace('/\s+/', ' ', $value)) : null
        );
    }

    // --------------------------------------------------------------------------
    // CPF (CRIPTO + HASH)
    // --------------------------------------------------------------------------
    protected function cpf(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Crypt::decryptString($value) : null,
            set: function ($value) {

                if (! $value) {
                    return null;
                }

                $cpf = preg_replace('/\D/', '', $value);

                $this->attributes['cpf_hash'] = hash('sha256', $cpf);

                return Crypt::encryptString($cpf);
            }
        );
    }

    // --------------------------------------------------------------------------
    // CNS (CRIPTO + HASH)
    // --------------------------------------------------------------------------
    protected function cns(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Crypt::decryptString($value) : null,
            set: function ($value) {

                if (! $value) {
                    return null;
                }

                $cns = preg_replace('/\D/', '', $value);

                $this->attributes['cns_hash'] = hash('sha256', $cns);

                return Crypt::encryptString($cns);
            }
        );
    }

    // --------------------------------------------------------------------------
    // EMAIL
    // --------------------------------------------------------------------------
    protected function email(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Crypt::decryptString($value) : null,
            set: fn ($value) => $value
                ? Crypt::encryptString(Str::lower(trim($value)))
                : null
        );
    }

    // --------------------------------------------------------------------------
    // TELEFONE (PADRÃO E.164 SIMPLIFICADO)
    // --------------------------------------------------------------------------
    protected function telefone(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Crypt::decryptString($value) : null,
            set: function ($value) {

                if (! $value) {
                    return null;
                }

                $numero = preg_replace('/\D/', '', $value);

                // força padrão Brasil (ex: 5592...)
                if (! Str::startsWith($numero, '55')) {
                    $numero = '55'.$numero;
                }

                return Crypt::encryptString($numero);
            }
        );
    }

    // --------------------------------------------------------------------------
    // RELACIONAMENTOS
    // --------------------------------------------------------------------------
    public function notificacoes()
    {
        return $this->hasMany(
            \App\Models\FilaNotificacao::class,
            'paciente_id',
            'id'
        );
    }

    // --------------------------------------------------------------------------
    // SCOPES
    // --------------------------------------------------------------------------
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    public function scopePorCpf($query, $cpf)
    {
        return $query->where(
            'cpf_hash',
            hash('sha256', preg_replace('/\D/', '', $cpf))
        );
    }

    public function scopePorCns($query, $cns)
    {
        return $query->where(
            'cns_hash',
            hash('sha256', preg_replace('/\D/', '', $cns))
        );
    }
}
