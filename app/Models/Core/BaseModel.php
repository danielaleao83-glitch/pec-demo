<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class BaseModel extends Model
{
    use \App\Traits\HasSensitiveDataAudit;

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = true;

    protected $dateFormat = 'Y-m-d H:i:s';

    protected $guarded = [
        'id',
        'created_by',
        'updated_by',
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    protected $hidden = ['deleted_at'];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        // =========================
        // CREATE
        // =========================
        static::creating(function ($model) {

            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }

            if (Auth::check()) {
                $model->created_by = Auth::id();
            }
        });

        static::created(function ($model) {

            if (! Schema::hasTable('auditorias')) {
                return;
            }

            \App\Models\Auditoria\Auditoria::registrarForense(
                'create',
                $model,
                null,
                $model->getAttributes()
            );
        });

        // =========================
        // UPDATE
        // =========================
        static::updating(function ($model) {

            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });

        static::updated(function ($model) {

            if (! Schema::hasTable('auditorias')) {
                return;
            }

            \App\Models\Auditoria\Auditoria::registrarForense(
                'update',
                $model,
                $model->getOriginal(),
                $model->getDirty()
            );
        });

        // =========================
        // DELETE
        // =========================
        static::deleted(function ($model) {

            if (! Schema::hasTable('auditorias')) {
                return;
            }

            \App\Models\Auditoria\Auditoria::registrarForense(
                'delete',
                $model,
                $model->getAttributes(),
                null
            );
        });
    }
}
