@extends('vida.saude.layouts.app')

@section('title', 'Painel Principal SOC')

@section('content')

<div class="space-y-8">

    {{-- 🔐 HEADER COM CONTEXTO DE SEGURANÇA --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

        <div>

            <h1 class="text-3xl font-bold text-white">
                Painel Clínico SOC
            </h1>

            <p class="text-blue-100/70 mt-2 text-sm">
                Plataforma clínica segura • Monitoramento ativo • Auditoria contínua
            </p>

            {{-- 🧠 CONTEXTO DE SEGURANÇA --}}
            <p class="text-xs text-white/40 mt-1">
                Sessão protegida • Log de auditoria ativo • Risk engine online
            </p>

        </div>

        <div class="bg-[#071427] border border-white/10 rounded-2xl px-4 py-3 text-sm text-blue-100/80">

            🔐 Sessão: {{ auth()->id() ?? 'anon' }} |
            🛰 SOC: ativo

        </div>

    </div>

    {{-- 📊 KPIs OPERACIONAIS (FUTURO SOC) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">

        <a href="{{ route('pacientes.index') }}"
           class="bg-[#071427] border border-white/10 rounded-2xl p-6 hover:border-blue-500/40 transition">

            <div class="text-white font-semibold text-lg">Pacientes</div>
            <div class="text-blue-100/60 text-sm mt-2">Cadastro clínico seguro</div>

        </a>

        <a href="{{ route('triagem.index') }}"
           class="bg-[#071427] border border-white/10 rounded-2xl p-6 hover:border-blue-500/40 transition">

            <div class="text-white font-semibold text-lg">Triagem</div>
            <div class="text-blue-100/60 text-sm mt-2">Classificação de risco</div>

        </a>

        <a href="{{ route('guiche.index') }}"
           class="bg-[#071427] border border-white/10 rounded-2xl p-6 hover:border-blue-500/40 transition">

            <div class="text-white font-semibold text-lg">Guichê</div>
            <div class="text-blue-100/60 text-sm mt-2">Fila em tempo real</div>

        </a>

        <a href="#"
           class="bg-[#071427] border border-white/10 rounded-2xl p-6 hover:border-blue-500/40 transition">

            <div class="text-white font-semibold text-lg">SOC Monitor</div>
            <div class="text-blue-100/60 text-sm mt-2">Segurança ativa</div>

        </a>

    </div>

    {{-- 🚨 STATUS DO SISTEMA (SOC LAYER) --}}
    <div class="bg-[#071427] border border-white/10 rounded-2xl p-6">

        <h2 class="text-white font-bold mb-4">
            🛰 Status do Sistema
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">

            <div class="text-green-300">
                ✔ Firewall ativo
            </div>

            <div class="text-green-300">
                ✔ Auditoria imutável
            </div>

            <div class="text-yellow-300">
                ⚠ Risk engine monitorando
            </div>

        </div>

    </div>

    {{-- 📡 FUTURO: STREAM EM TEMPO REAL --}}
    <div class="bg-[#071427] border border-white/10 rounded-2xl p-6">

        <h2 class="text-white font-bold mb-3">
            🛰 Stream Operacional (Tempo Real)
        </h2>

        <div class="h-24 flex items-center justify-center text-white/40 text-sm">

            WebSocket / Reverb / Pusher → eventos clínicos e segurança

        </div>

    </div>

</div>

@endsection