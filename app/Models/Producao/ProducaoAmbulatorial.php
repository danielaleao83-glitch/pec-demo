<?php

namespace App\Models\Producao;

use App\Models\Estabelecimentos\Unidade;
use App\Models\Profissional\Profissional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProducaoAmbulatorial extends Model
{
    protected $table = 'producao_ambulatorial';

    protected $fillable = [
        'unidade_id',
        'profissional_id',
        'competencia',
        'tipo', // BPA-I / BPA-C
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // -------------------------------------------
    // RELACIONAMENTOS
    // -------------------------------------------

    /**
     * 🏥 Unidade (CNES)
     */
    public function unidade()
    {
        return $this->belongsTo(Unidade::class);
    }

    /**
     * 👨‍⚕️ Profissional
     */
    public function profissional()
    {
        return $this->belongsTo(Profissional::class);
    }

    /**
     * 📋 Itens da produção
     */
    public function itens(): HasMany
    {
        return $this->hasMany(ItemProducao::class, 'producao_id');
    }

    // -------------------------------------------
    // HELPERS
    // -------------------------------------------

    /**
     * 📊 Total de procedimentos
     */
    public function totalItens(): int
    {
        return $this->itens->sum('quantidade');
    }

    /**
     * 📅 Formata competência (AAAAMM)
     */
    public function getCompetenciaFormatadaAttribute(): string
    {
        return substr($this->competencia, 0, 4).'-'.substr($this->competencia, 4, 2);
    }
}
