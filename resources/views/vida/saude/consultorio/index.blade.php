@extends('vida.saude.layouts.app')

@section('title', 'Consultório - Vida & Saúde')

@section('content')

<div class="container-fluid">

    {{-- 🏥 HEADER --}}
    <div class="row mb-4">

        <div class="col-12">

            <div class="card shadow-sm border-0">

                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>

                        <h4 class="mb-0">🏥 Consultório Médico</h4>

                        <small class="text-muted">
                            Atendimento clínico • Fluxo operacional • Sistema em tempo real
                        </small>

                    </div>

                    <div>

                        <span class="badge bg-success">ONLINE</span>
                        <span class="badge bg-dark">SOC ACTIVE</span>

                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- 📊 MÉTRICAS --}}
    <div class="row mb-4">

        <div class="col-md-3">
            <x-dashboard-card title="Fila Atual" value="--" color="primary" icon="👥" />
        </div>

        <div class="col-md-3">
            <x-dashboard-card title="Atendimentos" value="--" color="success" icon="🩺" />
        </div>

        <div class="col-md-3">
            <x-dashboard-card title="Urgências" value="--" color="danger" icon="🚨" />
        </div>

        <div class="col-md-3">
            <x-dashboard-card title="Tempo Médio" value="--" color="warning" icon="⏱" />
        </div>

    </div>

    {{-- 🚨 ALERTA SOC --}}
    <x-alert
        type="warning"
        message="Todos os atendimentos são monitorados e registrados em auditoria forense em tempo real."
    />

    {{-- 📋 FILA --}}
    <div class="card shadow-sm border-0 mt-3">

        <div class="card-header bg-white">

            <strong>📋 Fila de Atendimento</strong>

        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead class="table-dark">

                        <tr>

                            <th>Prioridade</th>
                            <th>Paciente</th>
                            <th>Documento</th>
                            <th>Status</th>
                            <th>Chegada</th>
                            <th>Ações</th>

                        </tr>

                    </thead>

                    <tbody>

                        {{-- LOOP REAL --}}
                        {{-- @foreach($fila as $item) --}}

                        <tr>

                            <td>
                                <x-badge type="danger" text="ALTA" />
                            </td>

                            <td>{{ e('Paciente Exemplo') }}</td>

                            <td>{{ e('***.***.***-**') }}</td>

                            <td>
                                <x-badge type="warning" text="AGUARDANDO" />
                            </td>

                            <td>12:40</td>

                            <td>

                                <button class="btn btn-sm btn-primary">
                                    Chamar
                                </button>

                                <button class="btn btn-sm btn-success">
                                    Atender
                                </button>

                            </td>

                        </tr>

                        {{-- @endforeach --}}

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    {{-- 🛰 MONITORAMENTO --}}
    <div class="card mt-4 shadow-sm border-0">

        <div class="card-header bg-white">

            🛰 Monitoramento em Tempo Real

        </div>

        <div class="card-body">

            <div id="realtime-feed" class="text-muted small">

                Aguardando eventos do sistema...

            </div>

        </div>

    </div>

</div>

@endsection

{{-- 🛰 SOCKET READY --}}
@push('scripts')

<script>

    Echo.channel('vida-saude-events')

        .listen('.event', (e) => {

            const container = document.getElementById('realtime-feed');

            const item = document.createElement('div');

            item.innerHTML = `
                🧠 ${e.tipo ?? 'EVENTO'} |
                IP: ${e.ip ?? 'N/A'} |
                STATUS: ${e.status ?? 'OK'}
            `;

            container.prepend(item);

        });

</script>

@endpush