<?php

namespace App\Models\Sistema;

use App\Traits\Auditable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use Auditable, HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'users';

    protected $fillable = [
        'uuid',
        'name',
        'email',
        'password',
        'role_id',
        'cns',
        'cbo',
        'tipo_profissional',
        'ativo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'ativo' => 'boolean',
    ];

    /*
    |----------------------------------------------------------
    | 🧬 UUID AUTOMÁTICO (RNDS / SUS)
    |----------------------------------------------------------
    */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    /*
    |----------------------------------------------------------
    | 🔗 RELAÇÕES
    |----------------------------------------------------------
    */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    /*
    |----------------------------------------------------------
    | 🔐 PERMISSÕES
    |----------------------------------------------------------
    */
    public function hasRole($role): bool
    {
        if ($this->roles()->where('name', $role)->exists()) {
            return true;
        }

        return $this->role?->name === $role;
    }

    public function hasAnyRole(array $roles): bool
    {
        if ($this->roles()->whereIn('name', $roles)->exists()) {
            return true;
        }

        return in_array($this->role?->name, $roles);
    }

    public function hasPermission($permission): bool
    {
        if ($this->roles()->whereHas('permissions', function ($q) use ($permission) {
            $q->where('name', $permission);
        })->exists()) {
            return true;
        }

        if ($this->role && $this->role->permissions()->where('name', $permission)->exists()) {
            return true;
        }

        return false;
    }

    /*
    |----------------------------------------------------------
    | 👑 ADMIN
    |----------------------------------------------------------
    */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /*
    |----------------------------------------------------------
    | 🔐 PASSWORD AUTO HASH
    |----------------------------------------------------------
    */
    public function setPasswordAttribute($value)
    {
        if (! $value) {
            return;
        }

        if (strlen($value) === 60) {
            $this->attributes['password'] = $value;

            return;
        }

        $this->attributes['password'] = bcrypt($value);
    }
}
