@extends('vida.saude.layouts.app')

@section('title', 'Evoluções SOAP')

@section('content')

<div class="space-y-6">

    {{-- 🧠 HEADER SOC --}}
    <div class="flex justify-between items-center">

        <div>
            <h1 class="text-2xl font-bold text-white">
                Evoluções SOAP
            </h1>

            <p class="text-blue-100/60 text-sm">
                Registro clínico estruturado com auditoria imutável
            </p>
        </div>

        <a href="#"
           class="bg-[#1976d2] hover:bg-blue-600 px-4 py-2 rounded-xl text-white">
            + Nova Evolução
        </a>

    </div>

    {{-- 🚨 ALERTA SOC --}}
    <div class="bg-blue-500/10 border border-blue-500/30 text-blue-200 p-4 rounded-2xl text-sm">
        ⚠ Todas as evoluções SOAP são registradas com hash de integridade e trilha de auditoria forense.
    </div>

    {{-- 📊 RESUMO --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

        <div class="bg-[#071427] p-4 rounded-2xl border border-white/10">
            <p class="text-white/60 text-sm">Total SOAP</p>
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

    {{-- 📋 TABELA SOAP --}}
    <div class="bg-[#071427] border border-white/10 rounded-2xl overflow-hidden">

        <table class="w-full text-sm text-white">

            <thead class="bg-[#1976d2] text-white">
                <tr>
                    <th class="px-4 py-3">Paciente</th>
                    <th class="px-4 py-3">Subjetivo</th>
                    <th class="px-4 py-3">Objetivo</th>
                    <th class="px-4 py-3">Avaliação</th>
                    <th class="px-4 py-3">Plano</th>
                    <th class="px-4 py-3">Profissional</th>
                    <th class="px-4 py-3">Data</th>
                    <th class="px-4 py-3">Hash</th>
                </tr>
            </thead>

            <tbody>

                @forelse($soaps as $soap)

                    <tr class="border-t border-white/5 hover:bg-white/5 transition">

                        <td class="px-4 py-3">
                            {{ $soap->paciente->nome ?? 'N/A' }}
                        </td>

                        <td class="px-4 py-3 text-white/70">
                            {{ Str::limit($soap->subjetivo, 40) }}
                        </td>

                        <td class="px-4 py-3 text-white/70">
                            {{ Str::limit($soap->objetivo, 40) }}
                        </td>

                        <td class="px-4 py-3 text-white/70">
                            {{ Str::limit($soap->avaliacao, 40) }}
                        </td>

                        <td class="px-4 py-3 text-white/70">
                            {{ Str::limit($soap->plano, 40) }}
                        </td>

                        <td class="px-4 py-3">
                            {{ $soap->profissional ?? 'Sistema' }}
                        </td>

                        <td class="px-4 py-3 text-white/60">
                            {{ $soap->created_at }}
                        </td>

                        <td class="px-4 py-3">
                            <code class="text-xs text-blue-300">
                                {{ Str::limit($soap->hash_integridade ?? '---', 10) }}
                            </code>
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="8" class="text-center py-10 text-white/50">
                            Nenhuma evolução SOAP registrada
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection