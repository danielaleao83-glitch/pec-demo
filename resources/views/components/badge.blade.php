@props([
    'type' => 'default',
])

@php
    $map = [
        'success' => 'bg-green-500 text-black',
        'warning' => 'bg-yellow-400 text-black',
        'danger'  => 'bg-red-500 text-white',
        'info'    => 'bg-blue-500 text-white',
        'default' => 'bg-gray-500 text-white',
    ];
@endphp

<span class="px-3 py-1 rounded-full text-xs font-bold {{ $map[$type] }}">
    {{ $slot }}
</span>