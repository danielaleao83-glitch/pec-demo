<?php

namespace App\Models\Sistema;

use App\Models\Sistema\BaseModel;
use App\Traits\HasSensitiveDataAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Role extends BaseModel
{
    use HasFactory, HasSensitiveDataAudit, SoftDeletes;

    protected $table = 'roles';

    protected $fillable = ['nome', 'descricao', 'registro_hash'];

    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];

    protected $casts = [
        'nome' => 'string',
        'descricao' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // --------------------------------------------------------------------------
    // RELACIONAMENTOS
    // --------------------------------------------------------------------------
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles', 'role_id', 'user_id')
            ->withTimestamps();
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_role', 'role_id', 'permission_id')
            ->withTimestamps();
    }

    // --------------------------------------------------------------------------
    // GERENCIAMENTO DE PERMISSÕES
    // --------------------------------------------------------------------------
    public function hasPermission(string $permission): bool
    {
        return $this->permissions->contains('nome', $permission);
    }

    public function assignPermission(int $permissionId): void
    {
        if (! $this->permissions()->where('permission_id', $permissionId)->exists()) {
            $this->permissions()->attach($permissionId);
        }
    }

    public function removePermission(int $permissionId): void
    {
        $this->permissions()->detach($permissionId);
    }

    // --------------------------------------------------------------------------
    // VALIDAÇÃO FORTE E HASH DE INTEGRIDADE
    // --------------------------------------------------------------------------
    protected function validarCampos(): void
    {
        if (! $this->nome || strlen($this->nome) < 3) {
            throw new \InvalidArgumentException('O nome da role é obrigatório e deve ter pelo menos 3 caracteres.');
        }
        if (! $this->descricao) {
            throw new \InvalidArgumentException('A descrição da role é obrigatória.');
        }

        // Cria hash de integridade para auditoria
        $this->registro_hash = hash('sha256', json_encode([
            $this->nome, $this->descricao,
        ]));
    }

    // --------------------------------------------------------------------------
    // EVENTOS DE AUDITORIA GOVERNAMENTAL
    // --------------------------------------------------------------------------
    protected static function booted()
    {
        static::creating(function ($model) {
            if (! Auth::check()) {
                abort(403);
            }
            $model->validarCampos();
            $model->registrarAcesso(
                Auth::id(),
                'create',
                'roles',
                null,
                null,
                $model->attributesToArray()
            );
        });

        static::updating(function ($model) {
            if (! Auth::check()) {
                abort(403);
            }
            $model->validarCampos();
            $model->registrarAcesso(
                Auth::id(),
                'update',
                'roles',
                $model->id,
                $model->getOriginal(),
                $model->getDirty()
            );
        });

        static::deleting(function ($model) {
            if (! Auth::check()) {
                abort(403);
            }
            $model->registrarAcesso(
                Auth::id(),
                'delete',
                'roles',
                $model->id,
                $model->attributesToArray(),
                null
            );
        });
    }

    // --------------------------------------------------------------------------
    // INDICES GOVERNAMENTAIS PARA PERFORMANCE
    // --------------------------------------------------------------------------
    public static function criarIndices()
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('roles')) {
            \Illuminate\Support\Facades\Schema::table('roles', function ($table) {
                $table->index('nome');
                $table->index('descricao');
            });
        }
    }
}
