<?php

namespace App\Policies;

use App\Models\AtendimentoSoap;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AtendimentoSoapObserver
{
    public function created(AtendimentoSoap $soap): void
    {
        Log::info('Atendimento SOAP criado', [
            'soap_id' => $soap->id,
            'user_id' => Auth::id(),
            'unidade_id' => $soap->unidade_id ?? null,
        ]);
    }

    public function updated(AtendimentoSoap $soap): void
    {
        Log::info('Atendimento SOAP atualizado', [
            'soap_id' => $soap->id,
            'user_id' => Auth::id(),
            'unidade_id' => $soap->unidade_id ?? null,
        ]);
    }

    public function deleted(AtendimentoSoap $soap): void
    {
        Log::warning('Atendimento SOAP deletado', [
            'soap_id' => $soap->id,
            'user_id' => Auth::id(),
            'unidade_id' => $soap->unidade_id ?? null,
        ]);
    }
}
