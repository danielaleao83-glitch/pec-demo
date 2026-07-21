<?php

namespace App\Imports;

use App\Models\HistoricoUsuario;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class HistoricoUsuariosImport implements ToModel, WithHeadingRow
{
    /**
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new HistoricoUsuario([
            'nome' => $row['nome'],
            'email' => $row['email'],
            'telefone' => $row['telefone'] ?? null,
            'cpf' => $row['cpf'] ?? null,
            'cargo' => $row['cargo'] ?? null,
            'data_nascimento' => isset($row['data_nascimento']) ? Carbon::parse($row['data_nascimento']) : null,
            'endereco' => $row['endereco'] ?? null,
        ]);
    }
}
