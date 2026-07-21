@props([
    'title' => 'Atenção',
])

<div class="fixed inset-0 bg-black/60 flex items-center justify-center hidden" id="modal">

    <div class="bg-[#071427] border border-white/10 rounded-2xl p-6 w-96">

        <h2 class="text-white text-lg font-bold mb-4">
            {{ $title }}
        </h2>

        <div class="text-white/70 text-sm mb-6">
            {{ $slot }}
        </div>

        <div class="flex justify-end space-x-2">

            <button class="px-4 py-2 bg-gray-600 text-white rounded-lg">
                Cancelar
            </button>

            <button class="px-4 py-2 bg-red-500 text-white rounded-lg">
                Confirmar
            </button>

        </div>

    </div>

</div>