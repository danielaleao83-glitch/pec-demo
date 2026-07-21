@props([
    'type' => 'info',
    'message' => '',
])

@php
    $map = [
        'success' => 'bg-green-500/20 text-green-200 border-green-500/30',
        'warning' => 'bg-yellow-500/20 text-yellow-200 border-yellow-500/30',
        'danger'  => 'bg-red-500/20 text-red-200 border-red-500/30',
        'info'    => 'bg-blue-500/20 text-blue-200 border-blue-500/30',
    ];
@endphp

<div class="border rounded-xl p-4 {{ $map[$type] ?? $map['info'] }}">
    {{ $message }}
</div>