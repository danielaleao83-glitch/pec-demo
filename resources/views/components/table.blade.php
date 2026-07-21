@props([
    'headers' => [],
])

<div class="overflow-x-auto">

    <table class="w-full text-sm text-left text-white">

        <thead class="bg-[#1976d2] text-white">

            <tr>
                @foreach($headers as $header)
                    <th class="px-6 py-4">
                        {{ $header }}
                    </th>
                @endforeach
            </tr>

        </thead>

        <tbody>

            {{ $slot }}

        </tbody>

    </table>

</div>