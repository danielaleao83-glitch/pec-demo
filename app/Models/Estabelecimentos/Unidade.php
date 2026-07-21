<?php

namespace App\Models\Estabelecimentos;

use App\Models\Permissoes\User;
use App\Models\Sistema\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Unidade extends BaseModel
{
    use SoftDeletes;

    protected $table = 'unidades';

    protected $fillable = [
        'nome',
        'cnes',
        'tipo',
        'municipio',
        'estado',
        'ativo',
        'origem_registro',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // =========================================================
    // RELACIONAMENTOS
    // =========================================================

    public function estabelecimento()
    {
        return $this->belongsTo(
            \App\Models\Estabelecimentos\Estabelecimento::class,
            'cnes',
            'cnes'
        );
    }

    public function profissionais()
    {
        return $this->belongsToMany(
            User::class,
            'profissional_unidade',
            'unidade_id',
            'user_id'
        )->withPivot(['cnes', 'cbo'])
            ->withTimestamps();
    }

    public function atendimentos()
    {
        return $this->hasMany(\App\Models\Atendimento\Atendimento::class);
    }

    // =========================================================
    // SCOPES
    // =========================================================

    public function scopePorMunicipio($query, string $municipio)
    {
        return $query->where('municipio', $municipio);
    }

    public function scopePorUF($query, string $uf)
    {
        return $query->where('estado', $uf);
    }

    public function scopeAtivas($query)
    {
        return $query->where('ativo', true);
    }

    // =========================================================
    // VALIDAÇÃO SUS
    // =========================================================

    protected function validar(): void
    {
        if (! $this->nome) {
            throw new \InvalidArgumentException('Nome da unidade é obrigatório.');
        }

        if (! $this->cnes || strlen($this->cnes) !== 7) {
            throw new \InvalidArgumentException('CNES inválido.');
        }

        if (! $this->tipo) {
            throw new \InvalidArgumentException('Tipo da unidade é obrigatório.');
        }

        if (! $this->estado || strlen($this->estado) !== 2) {
            throw new \InvalidArgumentException('UF inválido.');
        }

        if (! $this->municipio) {
            throw new \InvalidArgumentException('Município é obrigatório.');
        }
    }

    // =========================================================
    // BOOT (SEM DUPLICAR AUDITORIA)
    // =========================================================

    protected static function booted()
    {
        static::creating(function ($model) {

            $model->validar();

            if (Auth::check()) {
                $model->created_by = Auth::id();
            }
        });

        static::updating(function ($model) {

            $model->validar();

            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });

        // ⚠️ REMOVIDO registrarAcesso daqui
        // auditoria já é feita no BaseModel (centralizada)
    }

    // =========================================================
    // REGRAS DE NEGÓCIO
    // =========================================================

    public function possuiProfissionais(): bool
    {
        return $this->profissionais()->exists();
    }
}
