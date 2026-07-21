<?php

namespace App\Models\Sistema;

use App\Models\Sistema\BaseModel;
use App\Traits\HasSensitiveDataAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class HistoricoUsuario extends BaseModel
{
    use HasFactory, HasSensitiveDataAudit, SoftDeletes;

    protected $table = 'historico_usuarios';

    protected $fillable = [
        'nome',
        'email',
        'telefone',
        'cpf',
        'cargo',
        'data_nascimento',
        'endereco',
    ];

    protected $guarded = [
        'id', 'created_by', 'updated_by', 'deleted_at',
    ];

    protected $casts = [
        'data_nascimento' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // --------------------------------------------------------------------------
    // CRIPTOGRAFIA GOVERNAMENTAL ALTÍSSIMA
    // --------------------------------------------------------------------------
    protected function cpf()
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn ($value) => $value ? Crypt::decryptString($value) : null,
            set: fn ($value) => $value ? Crypt::encryptString(preg_replace('/\D/', '', $value)) : null // remove formatação
        );
    }

    protected function telefone()
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn ($value) => $value ? Crypt::decryptString($value) : null,
            set: fn ($value) => $value ? Crypt::encryptString(preg_replace('/\D/', '', $value)) : null
        );
    }

    protected function email()
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn ($value) => $value ? Crypt::decryptString($value) : null,
            set: fn ($value) => $value ? Crypt::encryptString(Str::lower(trim($value))) : null
        );
    }

    // --------------------------------------------------------------------------
    // SCOPES ALTAMENTE SEGUROS
    // --------------------------------------------------------------------------
    public function scopeAtivos($query)
    {
        return $query->whereNull('deleted_at');
    }

    public function scopePorNome($query, string $nome)
    {
        return $query->where('nome', 'like', "%{$nome}%");
    }

    // Nota: buscas por cpf/email exigem hash seguro para consulta
    public function scopePorCPF($query, string $cpf)
    {
        $cpf = preg_replace('/\D/', '', $cpf);
        $encrypted = Crypt::encryptString($cpf);

        return $query->where('cpf', $encrypted);
    }

    public function scopePorEmail($query, string $email)
    {
        $encrypted = Crypt::encryptString(Str::lower(trim($email)));

        return $query->where('email', $encrypted);
    }

    // --------------------------------------------------------------------------
    // AUDITORIA GOVERNAMENTAL BLINDADA
    // --------------------------------------------------------------------------
    protected static function booted()
    {
        static::creating(function ($model) {
            $userId = Auth::id();
            if ($userId) {
                $model->created_by = $userId;
            }

            $model->registrarAcesso(
                $userId,
                'create',
                'historico_usuario',
                $model->id ?? null,
                null,
                $model->attributesToArray(),
                hash('sha256', json_encode($model->attributesToArray())) // hash de integridade
            );
        });

        static::updating(function ($model) {
            $userId = Auth::id();
            if ($userId) {
                $model->updated_by = $userId;
            }

            $model->registrarAcesso(
                $userId,
                'update',
                'historico_usuario',
                $model->id,
                $model->getOriginal(),
                $model->getDirty(),
                hash('sha256', json_encode($model->getDirty()))
            );
        });

        static::deleting(function ($model) {
            $userId = Auth::id();
            $model->registrarAcesso(
                $userId,
                'delete',
                'historico_usuario',
                $model->id,
                $model->attributesToArray(),
                hash('sha256', json_encode($model->attributesToArray()))
            );
        });
    }

    // --------------------------------------------------------------------------
    // VALIDAÇÃO GOVERNAMENTAL
    // --------------------------------------------------------------------------
    protected function validarIntegridade(): void
    {
        if (! $this->nome) {
            throw new \InvalidArgumentException('Nome do usuário é obrigatório.');
        }

        if (! $this->cpf || strlen(preg_replace('/\D/', '', $this->cpf)) !== 11) {
            throw new \InvalidArgumentException('CPF inválido, deve ter 11 números.');
        }

        if (! $this->email || ! filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Email inválido.');
        }
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->validarIntegridade();
        });
    }
}
