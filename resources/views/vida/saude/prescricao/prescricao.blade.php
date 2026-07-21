@extends('vida.saude.layouts.app')

@section('title', 'Prescrições')

@section('content')

<div class="space-y-6">

    {{-- 🧠 HEADER SOC --}}
    <div class="flex justify-between items-center">

        <div>
            <h1 class="text-2xl font-bold text-white">
                Prescrições Médicas
            </h1>

            <p class="text-blue-100/60 text-sm">
                Registro clínico com auditoria e rastreabilidade total
            </p>
        </div>

        <a href="#"
           class="bg-[#1976d2] hover:bg-blue-600 px-4 py-2 rounded-xl text-white">
            + Nova Prescrição
        </a>

    </div>

    {{-- 📊 ALERTA SOC --}}
    <div class="bg-yellow-500/10 border border-yellow-500/30 text-yellow-200 p-4 rounded-2xl text-sm">
        ⚠ Todas as prescrições são registradas com hash imutável e auditadas em tempo real.
    </div>

    {{-- 📋 TABELA --}}
    <div class="bg-[#071427] border border-white/10 rounded-2xl overflow-hidden">

        <table class="w-full text-sm text-white">

            <thead class="bg-[#1976d2] text-white">
                <tr>
                    <th class="px-4 py-3">Paciente</th>
                    <th class="px-4 py-3">Medicamento</th>
                    <th class="px-4 py-3">Dosagem</th>
                    <th class="px-4 py-3">Data</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Ações</th>
                </tr>
            </thead>

            <tbody>

                @forelse($prescricoes as $prescricao)

                    <tr class="border-t border-white/5 hover:bg-white/5 transition">

                        <td class="px-4 py-3">
                            {{ $prescricao->paciente->nome ?? 'N/A' }}
                        </td>

                        <td class="px-4 py-3 text-blue-100/80">
                            {{ $prescricao->medicamento }}
                        </td>

                        <td class="px-4 py-3">
                            {{ $prescricao->dosagem }}
                        </td>

                        <td class="px-4 py-3 text-white/70">
                            {{ $prescricao->created_at }}
                        </td>

                        <td class="px-4 py-3">

                            @if($prescricao->status === 'ativa')
                                <span class="px-2 py-1 bg-green-500/20 text-green-300 rounded-lg text-xs">
                                    ATIVA
                                </span>
                            @else
                                <span class="px-2 py-1 bg-red-500/20 text-red-300 rounded-lg text-xs">
                                    ENCERRADA
                                </span>
                            @endif

                        </td>

                        <td class="px-4 py-3 flex gap-3">

                            <a href="#"
                               class="text-blue-400 hover:underline">
                                Ver
                            </a>

                            <a href="#"
                               class="text-yellow-400 hover:underline">
                                Editar
                            </a>

                            <a href="#"
                               class="text-red-400 hover:underline">
                                Cancelar
                            </a>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="6" class="text-center py-10 text-white/50">
                            Nenhuma prescrição encontrada
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    {{-- 🧠 PAINEL FORENSE --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <div class="bg-[#071427] p-4 rounded-2xl">
            <h3 class="text-sm text-white/70">Total</h3>
            <p class="text-xl font-bold text-white">--</p>
        </div>

        <div class="bg-[#071427] p-4 rounded-2xl">
            <h3 class="text-sm text-white/70">Ativas</h3>
            <p class="text-xl font-bold text-green-400">--</p>
        </div>

        <div class="bg-[#071427] p-4 rounded-2xl">
            <h3 class="text-sm text-white/70">Canceladas</h3>
            <p class="text-xl font-bold text-red-400">--</p>
        </div>

    </div>

</div>

@endsection