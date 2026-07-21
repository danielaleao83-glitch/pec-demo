<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'system' => 'eSUS_APS',
            'status' => 'online',

            'user' => $request->user(),
            'unidade' => $request->user()?->unidade_id,

            'modules' => [
                'dashboard',
                'atendimento',
                'paciente',
                'prescricao',
                'integracao'
            ],
        ]);
    }
}