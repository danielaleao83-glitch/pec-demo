<?php

namespace App\Models\Procedimentos;

use App\Models\Sistema\BaseModel;
use App\Traits\HasSensitiveDataAudit;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class Vacina extends BaseModel
{
    use HasSensitiveDataAudit, SoftDeletes;

    protected $table = 'vacinas';

    protected $fillable = [
        'codigo',
        'nome',
        'fabricante',
        'diluente',
        'categoria',
        'tipo',
        'descricao',
        'created_by',
        'updated_by',
    ];

    protected $guarded = ['id', 'deleted_at'];

    protected $casts = [
        'codigo' => 'string',
        'nome' => 'string',
        'fabricante' => 'string',
        'diluente' => 'string',
        'categoria' => 'string',
        'tipo' => 'string',
        'descricao' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // -------------------------------------------
    // BOOT (AUDITORIA AUTOMÁTICA)
    // -------------------------------------------
    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (Auth::check()) {
                $model->created_by = Auth::id();
            }
            $model->registrarAcesso(Auth::id(), 'create', 'vacinas', null, null, $model->attributesToArray());
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
            $model->registrarAcesso(Auth::id(), 'update', 'vacinas', $model->id, $model->getOriginal(), $model->getDirty());
        });

        static::deleting(function ($model) {
            $model->registrarAcesso(Auth::id(), 'delete', 'vacinas', $model->id, $model->attributesToArray(), null);
        });
    }

    // -------------------------------------------
    // VALIDAÇÃO FORTE
    // -------------------------------------------
    protected function validarCamposObrigatorios(): void
    {
        $validator = Validator::make($this->attributesToArray(), [
            'codigo' => 'required|string|max:20|unique:vacinas,codigo',
            'nome' => 'required|string|max:255',
            'fabricante' => 'nullable|string|max:255',
            'diluente' => 'nullable|string|max:100',
            'categoria' => 'nullable|string|max:100',
            'tipo' => 'nullable|string|max:100',
            'descricao' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }
    }
}
