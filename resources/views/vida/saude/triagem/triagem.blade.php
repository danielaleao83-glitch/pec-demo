@extends('vida.saude.layouts.app')

@section('title', 'Triagem Clínica')

@section('content')

<div class="space-y-6">

    {{-- 🧠 HEADER SOC --}}
    <div class="flex justify-between items-center">

        <div>
            <h1 class="text-2xl font-bold text-white">
                Triagem Clínica
            </h1>

            <p class="text-blue-100/60 text-sm">
                Classificação de risco + entrada assistencial monitorada
            </p>
        </div>

        <a href="#"
           class="bg-[#1976d2] hover:bg-blue-600 px-4 py-2 rounded-xl text-white">
            + Nova Triagem
        </a>

    </div>

    {{-- 🚨 ALERTA SOC --}}
    <div class="bg-red-500/10 border border-red-500/30 text-red-200 p-4 rounded-2xl text-sm">
        ⚠ Triagem registrada com fingerprint, score de risco e auditoria imutável.
    </div>

    {{-- 📊 RESUMO --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

        <div class="bg-[#071427] p-4 rounded-2xl border border-white/10">
            <p class="text-white/60 text-sm">Total Triagens</p>
            <p class="text-xl font-bold text-white">--</p>
        </div>

        <div class="bg-[#071427] p-4 rounded-2xl border border-white/10">
            <p class="text-white/60 text-sm">Vermelho (Crítico)</p>
            <p class="text-xl font-bold text-red-400">--</p>
        </div>

        <div class="bg-[#071427] p-4 rounded-2xl border border-white/10">
            <p class="text-white/60 text-sm">Amarelo</p>
            <p class="text-xl font-bold text-yellow-400">--</p>
        </div>

        <div class="bg-[#071427] p-4 rounded-2xl border border-white/10">
            <p class="text-white/60 text-sm">Verde</p>
            <p class="text-xl font-bold text-green-400">--</p>
        </div>

    </div>

    {{-- 📋 LISTA DE TRIAGEM --}}
    <div class="bg-[#071427] border border-white/10 rounded-2xl overflow-hidden">

        <table class="w-full text-sm text-white">

            <thead class="bg-[#1976d2] text-white">
                <tr>
                    <th class="px-4 py-3">Paciente</th>
                    <th class="px-4 py-3">Queixa</th>
                    <th class="px-4 py-3">Pressão</th>
                    <th class="px-4 py-3">FC</th>
                    <th class="px-4 py-3">Temperatura</th>
                    <th class="px-4 py-3">Risco</th>
                    <th class="px-4 py-3">Profissional</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Ações</th>
                </tr>
            </thead>

            <tbody>

                @forelse($triagens as $triagem)

                    <tr class="border-t border-white/5 hover:bg-white/5 transition">

                        <td class="px-4 py-3">
                            {{ $triagem->paciente->nome ?? 'N/A' }}
                        </td>

                        <td class="px-4 py-3 text-white/70">
                            {{ Str::limit($triagem->queixa_principal, 30) }}
                        </td>

                        <td class="px-4 py-3">
                            {{ $triagem->pressao ?? '--' }}
                        </td>

                        <td class="px-4 py-3">
                            {{ $triagem->frequencia_cardiaca ?? '--' }}
                        </td>

                        <td class="px-4 py-3">
                            {{ $triagem->temperatura ?? '--' }}
                        </td>

                        <td class="px-4 py-3">

                            @if(($triagem->risco ?? 0) >= 3)
                                <span class="px-2 py-1 bg-red-500/20 text-red-300 rounded-lg text-xs">
                                    CRÍTICO
                                </span>

                            @elseif(($triagem->risco ?? 0) == 2)
                                <span class="px-2 py-1 bg-yellow-500/20 text-yellow-300 rounded-lg text-xs">
                                    MODERADO
                                </span>

                            @else
                                <span class="px-2 py-1 bg-green-500/20 text-green-300 rounded-lg text-xs">
                                    BAIXO
                                </span>
                            @endif

                        </td>

                        <td class="px-4 py-3">
                            {{ $triagem->profissional ?? 'Sistema' }}
                        </td>

                        <td class="px-4 py-3">
                            <span class="text-white/70">
                                {{ $triagem->status ?? 'aguardando' }}
                            </span>
                        </td>

                        <td class="px-4 py-3 flex gap-3">

                            <a href="#" class="text-blue-400 hover:underline">
                                Ver
                            </a>

                            <a href="#" class="text-yellow-400 hover:underline">
                                Editar
                            </a>

                            <a href="#" class="text-red-400 hover:underline">
                                Encaminhar
                            </a>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="9" class="text-center py-10 text-white/50">
                            Nenhuma triagem registrada
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection