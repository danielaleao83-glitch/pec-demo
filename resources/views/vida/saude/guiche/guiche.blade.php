@extends('vida.saude.layouts.app')

@section('title', 'Guichê')

@section('content')

<div class="space-y-6">

    {{-- 🧠 HEADER --}}
    <div>

        <h1 class="text-3xl font-bold text-white">
            Guichê Clínico
        </h1>

        <p class="text-blue-100/60 mt-2 text-sm">
            Controle seguro de fila de atendimento • Monitoramento ativo
        </p>

    </div>

    {{-- 📋 TABELA --}}
    <div class="bg-[#071427] border border-white/10 rounded-2xl overflow-hidden">

        <div class="overflow-x-auto">

            <table class="w-full text-sm text-left text-white">

                <thead class="bg-[#1976d2] text-white">

                    <tr>

                        <th class="px-6 py-4">Paciente</th>
                        <th class="px-6 py-4">Setor</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Ação</th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($atendimentos as $atendimento)

                        <tr class="border-t border-white/5 hover:bg-white/5 transition"

                            data-uuid="{{ $atendimento->uuid ?? '' }}"
                            data-status="{{ $atendimento->status ?? '' }}"
                            data-setor="{{ $atendimento->tipo ?? '' }}">

                            {{-- 👤 PACIENTE --}}
                            <td class="px-6 py-4">

                                {{ e(optional($atendimento->paciente)->nome ?? 'N/A') }}

                            </td>

                            {{-- 🏥 SETOR --}}
                            <td class="px-6 py-4">

                                {{ e($atendimento->tipo ?? 'N/A') }}

                            </td>

                            {{-- 📊 STATUS --}}
                            <td class="px-6 py-4">

                                @php
                                    $status = strtolower($atendimento->status ?? '');
                                @endphp

                                @if($status === 'aguardando')
                                    <span class="text-yellow-300 font-semibold">AGUARDANDO</span>

                                @elseif($status === 'em_atendimento')
                                    <span class="text-green-300 font-semibold">EM ATENDIMENTO</span>

                                @elseif($status === 'finalizado')
                                    <span class="text-gray-400 font-semibold">FINALIZADO</span>

                                @else
                                    <span class="text-blue-200">DESCONHECIDO</span>
                                @endif

                            </td>

                            {{-- 🎯 AÇÃO --}}
                            <td class="px-6 py-4">

                                <form method="POST" action="{{ route('guiche.chamar') }}">

                                    @csrf

                                    <input
                                        type="hidden"
                                        name="uuid"
                                        value="{{ $atendimento->uuid }}"
                                    >

                                    <button
                                        type="submit"
                                        class="bg-yellow-400 hover:bg-yellow-300 text-black px-4 py-2 rounded-lg font-semibold transition"
                                        data-action="call-patient"
                                        data-uuid="{{ $atendimento->uuid }}"
                                    >
                                        Chamar
                                    </button>

                                </form>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="4" class="text-center px-6 py-10 text-blue-100/50">

                                Nenhum paciente aguardando.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection