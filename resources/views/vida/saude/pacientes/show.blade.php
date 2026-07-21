@extends('vida.saude.layouts.app')

@section('title', 'Paciente')

@section('content')

<div class="space-y-6">

    {{-- 🧠 HEADER --}}
    <div class="bg-[#071427] border border-white/10 rounded-2xl p-6">

        <h1 class="text-2xl font-bold">{{ $paciente->nome }}</h1>

        <p class="text-blue-100/60 text-sm mt-1">
            CPF/CNS: {{ $paciente->cpf ?? $paciente->cns }}
        </p>

    </div>

    {{-- 🧠 BLOCO CLÍNICO --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        <div class="bg-[#071427] p-6 rounded-2xl">
            <h2 class="font-semibold mb-2">Dados Clínicos</h2>
            <p class="text-white/60">Pronto para integração SOAP / e-SUS</p>
        </div>

        <div class="bg-[#071427] p-6 rounded-2xl">
            <h2 class="font-semibold mb-2">Auditoria SOC</h2>
            <p class="text-green-400">✔ Registro imutável ativo</p>
        </div>

    </div>

</div>

@endsection