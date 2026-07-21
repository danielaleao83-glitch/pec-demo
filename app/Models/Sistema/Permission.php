<?php

namespace App\Models\Sistema;

use App\Models\Sistema\BaseModel;
use App\Traits\HasSensitiveDataAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Permission extends BaseModel
{
    use HasFactory, HasSensitiveDataAudit, SoftDeletes;

    protected $table = 'permissions';

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
    // RELACIONAMENTO COM ROLES
    // --------------------------------------------------------------------------
    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'permission_role',
            'permission_id',
            'role_id'
        )->withTimestamps();
    }

    // --------------------------------------------------------------------------
    // GERENCIAMENTO DE ROLES
    // --------------------------------------------------------------------------
    public function assignRole(int $roleId): void
    {
        if (! $this->roles()->where('role_id', $roleId)->exists()) {
            $this->roles()->attach($roleId);
        }
    }

    public function removeRole(int $roleId): void
    {
        $this->roles()->detach($roleId);
    }

    // --------------------------------------------------------------------------
    // Validação forte para campos obrigatórios
    // --------------------------------------------------------------------------
    protected function validarCampos(): void
    {
        if (! $this->nome || strlen($this->nome) < 3) {
            throw new \InvalidArgumentException('O nome da permissão é obrigatório e deve ter pelo menos 3 caracteres.');
        }
        if (! $this->descricao) {
            throw new \InvalidArgumentException('A descrição da permissão é obrigatória.');
        }

        // Cria hash de integridade
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
                'permissions',
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
                'permissions',
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
                'permissions',
                $model->id,
                $model->attributesToArray(),
                null
            );
        });
    }

    // --------------------------------------------------------------------------
    // INDEX SUGERIDOS PARA PERFORMANCE GOVERNAMENTAL
    // --------------------------------------------------------------------------
    public static function criarIndices()
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('permissions')) {
            \Illuminate\Support\Facades\Schema::table('permissions', function ($table) {
                $table->index('nome');
                $table->index('descricao');
            });
        }
    }
}
