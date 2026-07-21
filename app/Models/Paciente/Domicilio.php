<?php

namespace App\Models\Paciente;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Domicilio extends Model
{
    protected $table = 'domicilios';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'cns_responsavel',
        'logradouro',
        'numero',
        'bairro',
        'cep',
        'municipio',
        'uf',
        'tipo_moradia',
        'condicao_moradia',
        'situacao_imovel',
        'microarea',
        'equipe_esf',
    ];

    protected static function booted()
    {
        static::creating(fn ($model) => $model->id = (string) Str::uuid());
    }

    public function familias()
    {
        return $this->hasMany(Familia::class);
    }
}
