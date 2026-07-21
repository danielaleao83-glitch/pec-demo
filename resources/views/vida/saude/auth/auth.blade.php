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
    <meta name="app-env" content="{{ app()->environment() }}">
    <meta name="session-id" content="{{ session()->getId() }}">
    <meta name="correlation-id" content="{{ \Illuminate\Support\Str::uuid() }}">

    <title>@yield('title', 'Vida/Saúde SOC - Prontuário Eletrônico')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[#030712] text-white antialiased">

    {{-- 🛰 BACKGROUND SOC LAYER --}}
    <div class="fixed inset-0 opacity-5 pointer-events-none">
        <div class="w-full h-full bg-gradient-to-br from-blue-900 to-black"></div>
    </div>

    {{-- 🧠 CONTAINER CENTRAL --}}
    <div class="min-h-screen flex items-center justify-center px-4 py-10 relative">

        {{-- 🔐 SOC HUD (DEBUG/FORENSE) --}}
        <div class="absolute top-4 right-4 text-[10px] text-white/30">
            SOC • SESSION: {{ session()->getId() }}
        </div>

        {{-- 🧱 CONTENT --}}
        @yield('content')

    </div>

    @stack('scripts')

    {{-- 🛰 FUTURO: WEBSOCKET / SOC EVENT BUS --}}
    <script>
        window.SOC = {
            session: "{{ session()->getId() }}",
            user: "{{ auth()->id() ?? null }}",
            env: "{{ app()->environment() }}",
            status: "secure",
            timestamp: "{{ now()->toISOString() }}"
        };
    </script>

</body>
</html>