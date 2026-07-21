<?php

namespace App\Observers;

use App\Models\Clinico\SinalVital;
use Illuminate\Support\Facades\Auth;

class SinalVitalObserver
{
    public function created(SinalVital $model)
    {
        $this->audit($model, 'create');
    }

    public function updated(SinalVital $model)
    {
        $this->audit($model, 'update');
    }

    public function deleted(SinalVital $model)
    {
        $this->audit($model, 'delete');
    }

    private function audit($model, $action)
    {
        if (!Auth::check()) return;

        $model->registrarAcesso(
            Auth::id(),
            $action,
            'sinais_vitais',
            $model->id,
            $model->getOriginal(),
            $model->getAttributes()
        );
    }
}