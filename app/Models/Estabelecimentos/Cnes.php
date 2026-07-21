<?php

namespace App\Models\Estabelecimentos;

use App\Models\Atendimento\Atendimento;
use App\Models\Sistema\BaseModel;
use App\Traits\HasSensitiveDataAudit;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Cnes extends BaseModel
{
    use HasSensitiveDataAudit, SoftDeletes;

    protected $table = 'cnes';

    protected $fillable = [
        'codigo',
        'nome',
        'municipio',
        'estado',
    ];

    protected $guarded = [
        'id', 'created_by', 'updated_by', 'deleted_at',
    ];

    protected $casts = [
        'codigo' => 'string',
        'nome' => 'string',
        'municipio' => 'string',
        'estado' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // -------------------------------------------
    // RELACIONAMENTO COM ATENDIMENTOS
    // -------------------------------------------
    public function atendimentos()
    {
        return $this->hasMany(Atendimento::class, 'cnes_id');
    }

    // -------------------------------------------
    // SCOPES
    // -------------------------------------------
    public function scopePorEstado($query, string $estado)
    {
        return $query->where('estado', $estado);
    }

    public function scopePorMunicipio($query, string $municipio)
    {
        return $query->where('municipio', $municipio);
    }

    public function scopeCodigo($query, string $codigo)
    {
        return $query->where('codigo', $codigo);
    }

    // -------------------------------------------
    // VALIDAÇÃO DE INTEGRIDADE E PADRÕES e-SUS / DATASUS
    // -------------------------------------------
    public function validarIntegridade(): void
    {
        if (! $this->codigo || strlen($this->codigo) !== 7) {
            throw new \InvalidArgumentException('Código CNES inválido, deve ter 7 caracteres.');
        }

        if (! $this->nome) {
            throw new \InvalidArgumentException('Nome do estabelecimento é obrigatório.');
        }

        if (! $this->estado || strlen($this->estado) !== 2) {
            throw new \InvalidArgumentException('Estado inválido, deve ter 2 caracteres.');
        }

        if (! $this->municipio) {
            throw new \InvalidArgumentException('Município é obrigatório.');
        }
    }

    // -------------------------------------------
    // HOOKS DE AUDITORIA E VALIDAÇÃO
    // -------------------------------------------
    protected static function booted(): void
    {
        // Auditoria governamental
        static::creating(function ($model) {
            if (Auth::check()) {
                $model->created_by = Auth::id();
            }
            $model->registrarAcesso(Auth::id(), 'create', 'cnes', null, null, $model->attributesToArray());
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
            $model->registrarAcesso(Auth::id(), 'update', 'cnes', $model->id, $model->getOriginal(), $model->getDirty());
        });

        static::deleting(function ($model) {
            $model->registrarAcesso(Auth::id(), 'delete', 'cnes', $model->id, $model->attributesToArray(), null);
        });

        // Validação de integridade antes de salvar
        static::saving(function ($model) {
            $model->validarIntegridade();
        });
    }
}
