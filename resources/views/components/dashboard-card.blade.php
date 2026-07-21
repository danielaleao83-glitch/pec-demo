@props([
    'title' => '',
    'value' => '',
    'icon' => null,
    'color' => 'blue',
])

<div class="bg-[#071427] border border-white/10 rounded-2xl p-5 shadow-sm">

    <div class="flex items-center justify-between">

        <div>

            <p class="text-white/60 text-sm">
                {{ $title }}
            </p>

            <h2 class="text-2xl font-bold text-white mt-1">
                {{ $value }}
            </h2>

        </div>

        @if($icon)
            <div class="text-3xl text-{{ $color }}-400">
                {{ $icon }}
            </div>
        @endif

    </div>

</div>