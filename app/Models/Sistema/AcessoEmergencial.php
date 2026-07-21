<?php

namespace App\Models\Sistema;

use App\Models\Paciente\Paciente;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcessoEmergencial extends Model
{
    use HasFactory;

    protected $table = 'acessos_emergenciais';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'paciente_id',
        'motivo',
        'ip',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | 🔗 RELACIONAMENTOS
    |--------------------------------------------------------------------------
    */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }
}
