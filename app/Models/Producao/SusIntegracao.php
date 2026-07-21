<?php

namespace App\Models\Producao;

use App\Traits\HasSensitiveDataAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class SusIntegracao extends BaseModel
{
    use HasFactory, HasSensitiveDataAudit, SoftDeletes;

    protected $table = 'sus_integracoes';

    /*
    |----------------------------------------------------------------------
    | Campos preenchíveis
    |----------------------------------------------------------------------
    */
    protected $fillable = [
        'codigo_municipio',
        'nome_municipio',
        'uf',
        'cnes',
        'status_integracao',
    ];

    /*
    |----------------------------------------------------------------------
    | Casts de atributos
    |----------------------------------------------------------------------
    */
    protected $casts = [
        'status_integracao' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /*
    |----------------------------------------------------------------------
    | Eventos automáticos com auditoria governamental
    |----------------------------------------------------------------------
    */
    protected static function booted()
    {
        static::creating(function ($model) {
            if (Auth::check()) {
                $model->created_by = Auth::id();
            }

            $model->registrarAcesso(
                Auth::id() ?? null,
                'create',
                'sus_integracoes',
                $model->id ?? null,
                null,
                $model->attributesToArray()
            );
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }

            $model->registrarAcesso(
                Auth::id() ?? null,
                'update',
                'sus_integracoes',
                $model->id,
                $model->getOriginal(),
                $model->getDirty()
            );
        });

        static::deleting(function ($model) {
            $model->registrarAcesso(
                Auth::id() ?? null,
                'delete',
                'sus_integracoes',
                $model->id,
                $model->attributesToArray(),
                null
            );
        });
    }

    /*
    |----------------------------------------------------------------------
    | Scopes úteis
    |----------------------------------------------------------------------
    */
    public function scopeAtivos($query)
    {
        return $query->where('status_integracao', true)->whereNull('deleted_at');
    }

    public function scopePorMunicipio($query, string $codigoMunicipio)
    {
        return $query->where('codigo_municipio', $codigoMunicipio);
    }

    public function scopePorUF($query, string $uf)
    {
        return $query->where('uf', $uf);
    }
}
