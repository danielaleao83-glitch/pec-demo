<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- 🔐 SEGURANÇA BASE --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex,nofollow">

    {{-- 🧠 CONTEXTO SOC --}}
    <meta name="app-soc" content="active">
    <meta name="session-id" content="{{ session()->getId() }}">
    <meta name="user-id" content="{{ auth()->id() }}">
    <meta name="timestamp" content="{{ now()->toISOString() }}">

    <title>@yield('title', 'Vida/Saúde SOC')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#030712] text-white antialiased min-h-screen">

<div class="min-h-screen flex">

    {{-- 🛰 SIDEBAR SOC --}}
    <aside class="hidden lg:flex lg:w-72 bg-[#071427] border-r border-white/10 flex-col">

        <div class="p-6 border-b border-white/10">

            <h1 class="text-xl font-bold text-white">
                Vida/Saúde SOC
            </h1>

            <p class="text-sm text-blue-100/60 mt-1">
                Prontuário Eletrônico Seguro
            </p>

            {{-- 🔐 STATUS DA SESSÃO --}}
            <div class="mt-3 text-xs text-white/40">
                Sessão: {{ session()->getId() }}
            </div>

        </div>

        {{-- 🧭 MENU --}}
        <nav class="flex-1 p-4 space-y-2">

            <a href="{{ route('dashboard') }}"
               class="block px-4 py-3 rounded-xl hover:bg-[#1976d2]/20 transition">
                Dashboard SOC
            </a>

            <a href="{{ route('pacientes.index') }}"
               class="block px-4 py-3 rounded-xl hover:bg-[#1976d2]/20 transition">
                Pacientes
            </a>

            <a href="{{ route('triagem.index') }}"
               class="block px-4 py-3 rounded-xl hover:bg-[#1976d2]/20 transition">
                Triagem
            </a>

            <a href="{{ route('guiche.index') }}"
               class="block px-4 py-3 rounded-xl hover:bg-[#1976d2]/20 transition">
                Guichê
            </a>

        </nav>

        {{-- 🛰 STATUS SOC --}}
        <div class="p-4 border-t border-white/10 text-xs space-y-1">

            <div class="text-green-400">✔ Firewall ativo</div>
            <div class="text-green-400">✔ Auditoria imutável</div>
            <div class="text-yellow-300">⚠ Risk engine ativo</div>

        </div>

    </aside>

    {{-- 🧠 MAIN CONTENT --}}
    <main class="flex-1 p-6 lg:p-10 relative">

        {{-- 🛰 HUD SOC (FIXO) --}}
        <div class="absolute top-4 right-4 text-xs text-white/40">
            SOC ACTIVE • {{ now()->format('H:i:s') }}
        </div>

        @yield('content')

    </main>

</div>

@stack('scripts')

{{-- 🛰 FUTURO: WEBSOCKET HOOK --}}
<script>
    window.SOC = {
        session: "{{ session()->getId() }}",
        user: "{{ auth()->id() }}",
        status: "active"
    };
</script>

</body>
</html>