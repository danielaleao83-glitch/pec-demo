@extends('vida.saude.layouts.app')

@section('title', 'Procedimentos')

@section('content')

<div class="space-y-6">

    {{-- 🧠 HEADER SOC --}}
    <div class="flex justify-between items-center">

        <div>
            <h1 class="text-2xl font-bold text-white">
                Procedimentos Clínicos
            </h1>

            <p class="text-blue-100/60 text-sm">
                Registro técnico assistencial com auditoria imutável
            </p>
        </div>

        <a href="#"
           class="bg-[#1976d2] hover:bg-blue-600 px-4 py-2 rounded-xl text-white">
            + Novo Procedimento
        </a>

    </div>

    {{-- 🚨 ALERTA SOC --}}
    <div class="bg-red-500/10 border border-red-500/30 text-red-200 p-4 rounded-2xl text-sm">
        ⚠ Todo procedimento gera trilha de auditoria, hash de integridade e correlação clínica.
    </div>

    {{-- 📊 RESUMO --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

        <div class="bg-[#071427] p-4 rounded-2xl border border-white/10">
            <p class="text-white/60 text-sm">Total</p>
            <p class="text-xl font-bold text-white">--</p>
        </div>

        <div class="bg-[#071427] p-4 rounded-2xl border border-white/10">
            <p class="text-white/60 text-sm">Hoje</p>
            <p class="text-xl font-bold text-blue-400">--</p>
        </div>

        <div class="bg-[#071427] p-4 rounded-2xl border border-white/10">
            <p class="text-white/60 text-sm">Críticos</p>
            <p class="text-xl font-bold text-red-400">--</p>
        </div>

        <div class="bg-[#071427] p-4 rounded-2xl border border-white/10">
            <p class="text-white/60 text-sm">Auditados</p>
            <p class="text-xl font-bold text-green-400">100%</p>
        </div>

    </div>

    {{-- 📋 TABELA --}}
    <div class="bg-[#071427] border border-white/10 rounded-2xl overflow-hidden">

        <table class="w-full text-sm text-white">

            <thead class="bg-[#1976d2] text-white">
                <tr>
                    <th class="px-4 py-3">Paciente</th>
                    <th class="px-4 py-3">Procedimento</th>
                    <th class="px-4 py-3">CID</th>
                    <th class="px-4 py-3">Profissional</th>
                    <th class="px-4 py-3">Data</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Ações</th>
                </tr>
            </thead>

            <tbody>

                @forelse($procedimentos as $procedimento)

                    <tr class="border-t border-white/5 hover:bg-white/5 transition">

                        <td class="px-4 py-3">
                            {{ $procedimento->paciente->nome ?? 'N/A' }}
                        </td>

                        <td class="px-4 py-3">
                            {{ $procedimento->nome }}
                        </td>

                        <td class="px-4 py-3 text-blue-100/70">
                            {{ $procedimento->cid ?? '---' }}
                        </td>

                        <td class="px-4 py-3">
                            {{ $procedimento->profissional ?? 'Sistema' }}
                        </td>

                        <td class="px-4 py-3 text-white/70">
                            {{ $procedimento->created_at }}
                        </td>

                        <td class="px-4 py-3">

                            @if($procedimento->status === 'executado')
                                <span class="px-2 py-1 bg-green-500/20 text-green-300 rounded-lg text-xs">
                                    EXECUTADO
                                </span>
                            @elseif($procedimento->status === 'pendente')
                                <span class="px-2 py-1 bg-yellow-500/20 text-yellow-300 rounded-lg text-xs">
                                    PENDENTE
                                </span>
                            @else
                                <span class="px-2 py-1 bg-red-500/20 text-red-300 rounded-lg text-xs">
                                    CANCELADO
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
                                Anular
                            </a>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="7" class="text-center py-10 text-white/50">
                            Nenhum procedimento registrado
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection