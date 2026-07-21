<?php

namespace App\Models\Sistema;

class ConfiguracaoMunicipal extends BaseModel
{
    // --------------------------------------------------------------------------
    // TABELA
    // --------------------------------------------------------------------------
    protected $table = 'configuracoes_municipais';

    // --------------------------------------------------------------------------
    // CAMPOS PREENCHÍVEIS
    // --------------------------------------------------------------------------
    protected $fillable = [
        'nome_municipio',
        'uf',
        'codigo_ibge',
        'endereco_prefeitura',
        'telefone',
        'email',
        'observacoes',
    ];

    // --------------------------------------------------------------------------
    // CAMPOS PROTEGIDOS
    // --------------------------------------------------------------------------
    protected $guarded = [
        'id',
        'created_by',
        'updated_by',
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    // --------------------------------------------------------------------------
    // CASTS DE ATRIBUTOS
    // --------------------------------------------------------------------------
    protected $casts = [
        'created_at' => 'immutable_datetime',
        'updated_at' => 'immutable_datetime',
        'deleted_at' => 'immutable_datetime',
    ];

    // --------------------------------------------------------------------------
    // RELACIONAMENTOS FUTUROS
    // --------------------------------------------------------------------------
    // Exemplo: relacionar com usuários que criaram ou atualizaram a configuração
    public function criador()
    {
        return $this->belongsTo(\App\Models\Permissoes\User::class, 'created_by');
    }

    public function atualizador()
    {
        return $this->belongsTo(\App\Models\Permissoes\User::class, 'updated_by');
    }

    // --------------------------------------------------------------------------
    // SCOPES ÚTEIS
    // --------------------------------------------------------------------------
    public function scopePorUF($query, string $uf)
    {
        return $query->where('uf', $uf);
    }

    public function scopePorMunicipio($query, string $municipio)
    {
        return $query->where('nome_municipio', $municipio);
    }
}
