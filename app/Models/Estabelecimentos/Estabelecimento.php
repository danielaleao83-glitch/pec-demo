<?php

namespace App\Models\Estabelecimentos;

use App\Models\Atendimento\Atendimento;
use App\Models\Atendimento\Profissional;
use App\Models\Sistema\BaseModel;
use App\Traits\HasSensitiveDataAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Estabelecimento extends BaseModel
{
    use HasFactory, HasSensitiveDataAudit, SoftDeletes;

    protected $table = 'estabelecimentos';

    protected $fillable = ['cnes', 'nome', 'municipio', 'uf'];

    protected $guarded = ['id', 'created_by', 'updated_by', 'deleted_at'];

    protected $casts = [
        'cnes' => 'string',
        'nome' => 'string',
        'municipio' => 'string',
        'uf' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // --------------------------------------------------------------------------
    // RELACIONAMENTOS
    // --------------------------------------------------------------------------
    public function profissionais()
    {
        return $this->hasMany(Profissional::class, 'estabelecimento_id');
    }

    public function atendimentos()
    {
        return $this->hasMany(Atendimento::class, 'estabelecimento_id');
    }

    public function cnes()
    {
        return $this->belongsTo(Cnes::class, 'cnes', 'codigo');
    }

    // --------------------------------------------------------------------------
    // SCOPES
    // --------------------------------------------------------------------------
    public function scopeAtivos($query)
    {
        return $query->whereNull('deleted_at');
    }

    public function scopePorMunicipio($query, string $municipio)
    {
        return $query->where('municipio', $municipio);
    }

    public function scopePorUF($query, string $uf)
    {
        return $query->where('uf', $uf);
    }

    public function scopePorCnes($query, string $cnes)
    {
        return $query->where('cnes', $cnes);
    }

    // --------------------------------------------------------------------------
    // AUDITORIA E VALIDAÇÃO
    // --------------------------------------------------------------------------
    protected static function booted()
    {
        // Criando
        static::creating(function ($model) {
            $userId = Auth::id();

            if (! $model->cnes || strlen($model->cnes) !== 7) {
                throw new \InvalidArgumentException('CNES inválido, deve ter 7 caracteres.');
            }
            if (! $model->nome) {
                throw new \InvalidArgumentException('Nome do estabelecimento é obrigatório.');
            }
            if (! $model->uf || strlen($model->uf) !== 2) {
                throw new \InvalidArgumentException('UF inválido, deve ter 2 caracteres.');
            }
            if (! $model->municipio) {
                throw new \InvalidArgumentException('Município é obrigatório.');
            }

            $model->registrarAcesso(
                $userId,
                'create',
                'estabelecimento',
                null,
                null,
                $model->attributesToArray()
            );
        });

        // Atualizando
        static::updating(function ($model) {
            $userId = Auth::id();
            $model->registrarAcesso(
                $userId,
                'update',
                'estabelecimento',
                $model->id,
                $model->getOriginal(),
                $model->getDirty()
            );
        });

        // Deletando
        static::deleting(function ($model) {
            $userId = Auth::id();
            $model->registrarAcesso(
                $userId,
                'delete',
                'estabelecimento',
                $model->id,
                $model->attributesToArray(),
                null
            );
        });
    }
}
