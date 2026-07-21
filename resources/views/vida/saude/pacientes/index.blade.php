@extends('vida.saude.layouts.app')

@section('title', 'Pacientes')

@section('content')

<div class="space-y-6">

    {{-- 🧠 HEADER SOC --}}
    <div class="flex justify-between items-center">

        <div>
            <h1 class="text-2xl font-bold text-white">Pacientes</h1>
            <p class="text-blue-100/60 text-sm">
                Cadastro clínico com auditoria ativa
            </p>
        </div>

        <a href="{{ route('pacientes.create') }}"
           class="bg-[#1976d2] hover:bg-blue-600 px-4 py-2 rounded-xl text-white">
            + Novo Paciente
        </a>

    </div>

    {{-- 📊 TABELA --}}
    <div class="bg-[#071427] border border-white/10 rounded-2xl overflow-hidden">

        <table class="w-full text-sm text-white">

            <thead class="bg-[#1976d2] text-white">
                <tr>
                    <th class="px-4 py-3">Nome</th>
                    <th class="px-4 py-3">CPF/CNS</th>
                    <th class="px-4 py-3">Nascimento</th>
                    <th class="px-4 py-3">Ações</th>
                </tr>
            </thead>

            <tbody>

                @forelse($pacientes as $paciente)

                    <tr class="border-t border-white/5 hover:bg-white/5">

                        <td class="px-4 py-3">
                            {{ $paciente->nome }}
                        </td>

                        <td class="px-4 py-3 text-blue-100/70">
                            {{ $paciente->cpf ?? $paciente->cns }}
                        </td>

                        <td class="px-4 py-3">
                            {{ $paciente->data_nascimento }}
                        </td>

                        <td class="px-4 py-3 flex gap-2">

                            <a href="{{ route('pacientes.show', $paciente->id) }}"
                               class="text-blue-400">
                                Ver
                            </a>

                            <a href="{{ route('pacientes.edit', $paciente->id) }}"
                               class="text-yellow-400">
                                Editar
                            </a>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="4" class="text-center py-10 text-white/50">
                            Nenhum paciente encontrado
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection