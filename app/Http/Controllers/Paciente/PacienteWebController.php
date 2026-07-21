<?php

namespace App\Http\Controllers\Paciente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PacienteWebController extends Controller
{
    public function index()
    {
        return response()->json(['module' => 'Paciente', 'controller' => 'PacienteWebController', 'action' => 'index']);
    }

    public function store(Request $request)
    {
        return response()->json(['message' => 'created']);
    }

    public function show($id)
    {
        return response()->json(['id' => $id]);
    }

    public function update(Request $request, $id)
    {
        return response()->json(['message' => 'updated', 'id' => $id]);
    }

    public function destroy($id)
    {
        return response()->json(['message' => 'deleted', 'id' => $id]);
    }
}