<?php

namespace App\Models\Producao;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SisabEnvio extends Model
{
    protected $table = 'sisab_envios';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'data_envio',
        'tipo',
        'payload',
        'xml_gerado',
        'status',
        'erro',
    ];

    protected $casts = [
        'payload' => 'array',
        'data_envio' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }
}
