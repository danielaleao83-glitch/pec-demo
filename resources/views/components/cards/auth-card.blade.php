@props([
    'title' => 'Vida/Saúde',
    'subtitle' => 'Prontuário Eletrônico',
])

<div class="w-full max-w-md bg-[#071427] border border-white/10 rounded-2xl shadow-2xl overflow-hidden">

    <div class="px-8 py-8 border-b border-white/10">

        <h1 class="text-2xl font-bold text-white">
            {{ $title }}
        </h1>

        <p class="text-sm text-blue-100/70 mt-2">
            {{ $subtitle }}
        </p>

    </div>

    <div class="p-8">

        {{ $slot }}

    </div>

</div>