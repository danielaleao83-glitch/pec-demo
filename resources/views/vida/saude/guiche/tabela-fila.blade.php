<div class="overflow-x-auto">

    <table class="w-full text-sm text-left text-white">

        <thead class="bg-[#1976d2] text-white">

            <tr>

                <th class="px-6 py-4">Paciente</th>
                <th class="px-6 py-4">Documento</th>
                <th class="px-6 py-4">Prioridade</th>
                <th class="px-6 py-4">Status</th>
                <th class="px-6 py-4">Entrada</th>
                <th class="px-6 py-4">Ação</th>

            </tr>

        </thead>

        <tbody>

            @forelse($atendimentos as $atendimento)

                <tr
                    class="border-t border-white/5 hover:bg-white/5 transition"

                    data-uuid="{{ $atendimento->uuid ?? '' }}"
                    data-status="{{ $atendimento->status ?? '' }}"
                    data-prioridade="{{ $atendimento->prioridade ?? 0 }}"
                >

                    {{-- 👤 PACIENTE --}}
                    <td class="px-6 py-4">

                        {{ e(optional($atendimento->paciente)->nome ?? 'N/A') }}

                    </td>

                    {{-- 🆔 DOCUMENTO --}}
                    <td class="px-6 py-4 text-blue-100/70">

                        {{ e(optional($atendimento->paciente)->documento ?? '---') }}

                    </td>

                    {{-- 🚨 PRIORIDADE --}}
                    <td class="px-6 py-4">

                        @php
                            $p = (int) ($atendimento->prioridade ?? 0);
                        @endphp

                        @if($p >= 3)
                            <span class="text-red-400 font-bold">ALTA</span>

                        @elseif($p == 2)
                            <span class="text-yellow-300 font-semibold">MÉDIA</span>

                        @elseif($p == 1)
                            <span class="text-blue-300">BAIXA</span>

                        @else
                            <span class="text-gray-400">NORMAL</span>
                        @endif

                    </td>

                    {{-- 📊 STATUS --}}
                    <td class="px-6 py-4">

                        @php
                            $status = strtolower($atendimento->status ?? '');
                        @endphp

                        @switch($status)

                            @case('aguardando')
                                <span class="text-yellow-300 font-semibold">AGUARDANDO</span>
                            @break

                            @case('chamado')
                                <span class="text-blue-300 font-semibold">CHAMADO</span>
                            @break

                            @case('em_atendimento')
                                <span class="text-green-300 font-semibold">EM ATENDIMENTO</span>
                            @break

                            @case('finalizado')
                                <span class="text-gray-400 font-semibold">FINALIZADO</span>
                            @break

                            @default
                                <span class="text-white/60">DESCONHECIDO</span>

                        @endswitch

                    </td>

                    {{-- ⏱ ENTRADA --}}
                    <td class="px-6 py-4 text-white/70">

                        {{ $atendimento->created_at?->format('H:i') ?? '--:--' }}

                    </td>

                    {{-- 🎯 AÇÃO --}}
                    <td class="px-6 py-4">

                        <form method="POST" action="{{ route('guiche.chamar') }}">

                            @csrf

                            <input
                                type="hidden"
                                name="uuid"
                                value="{{ $atendimento->uuid }}"
                            >

                            <button
                                type="submit"
                                class="bg-yellow-400 hover:bg-yellow-300 text-black px-4 py-2 rounded-lg font-semibold transition"
                                data-action="chamar"
                                data-uuid="{{ $atendimento->uuid }}"
                            >
                                Chamar
                            </button>

                        </form>

                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="6" class="text-center px-6 py-10 text-blue-100/50">

                        Nenhum paciente na fila no momento.

                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>

</div>