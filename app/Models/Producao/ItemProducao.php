<?php

namespace App\Models\Producao;

use App\Models\Procedimentos\Procedimento;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemProducao extends Model
{
    protected $table = 'itens_producao';

    protected $fillable = [
        'producao_id',
        'procedimento_id',
        'quantidade',
        'data_execucao',
    ];

    protected $casts = [
        'data_execucao' => 'datetime',
    ];

    // -------------------------------------------
    // RELACIONAMENTOS
    // -------------------------------------------

    public function producao(): BelongsTo
    {
        return $this->belongsTo(ProducaoAmbulatorial::class);
    }

    public function procedimento(): BelongsTo
    {
        return $this->belongsTo(Procedimento::class);
    }
}
