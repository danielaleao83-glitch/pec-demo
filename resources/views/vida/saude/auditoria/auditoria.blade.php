@extends('vida.saude.layouts.app')

@section('title', 'Auditoria SOC - Sistema Hospitalar')

@section('content')

<div class="container-fluid">

    {{-- 🔐 HEADER SOC --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>
                        <h4 class="mb-0">🛡 Auditoria SOC Hospitalar</h4>
                        <small class="text-muted">
                            Trilha forense • Imutabilidade • Monitoramento em tempo real
                        </small>
                    </div>

                    <div class="text-end">
                        <span class="badge bg-dark">BLOCKCHAIN LOG ACTIVE</span>
                        <span class="badge bg-success">REAL TIME</span>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- 📊 MÉTRICAS DE AUDITORIA --}}
    <div class="row mb-4">

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Total de Eventos</h6>
                    <h3>--</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Eventos Críticos</h6>
                    <h3 class="text-danger">--</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Integridade Hash</h6>
                    <h3 class="text-success">100%</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Alertas SOC</h6>
                    <h3 class="text-warning">--</h3>
                </div>
            </div>
        </div>

    </div>

    {{-- 🚨 ALERTA SOC --}}
    <div class="alert alert-warning shadow-sm">
        ⚠ Monitoramento ativo: todos os eventos são registrados com hash imutável
    </div>

    {{-- 📋 TABELA DE AUDITORIA --}}
    <div class="card shadow-sm border-0">

        <div class="card-header bg-white">
            <strong>Trilha de Auditoria Forense</strong>
        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Usuário</th>
                            <th>Ação</th>
                            <th>Módulo</th>
                            <th>IP</th>
                            <th>Rota</th>
                            <th>Hash</th>
                            <th>Status SOC</th>
                        </tr>
                    </thead>

                    <tbody>

                        {{-- EXEMPLO (substituir por @foreach) --}}
                        <tr>
                            <td>2026-05-10 12:00</td>
                            <td>admin</td>
                            <td>LOGIN</td>
                            <td>AUTENTICAÇÃO</td>
                            <td>192.168.0.1</td>
                            <td>/login</td>

                            <td>
                                <code class="text-muted">
                                    a8f3...91c2
                                </code>
                            </td>

                            <td>
                                <span class="badge bg-success">ÍNTEGRO</span>
                            </td>
                        </tr>

                        <tr>
                            <td>2026-05-10 12:05</td>
                            <td>system</td>
                            <td>FIREWALL_BLOCK</td>
                            <td>SEGURANÇA</td>
                            <td>189.10.10.5</td>
                            <td>/api/pacientes</td>

                            <td>
                                <code class="text-danger">
                                    b91d...ff99
                                </code>
                            </td>

                            <td>
                                <span class="badge bg-danger">BLOQUEADO</span>
                            </td>
                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    {{-- 🧠 PAINEL FORENSE --}}
    <div class="row mt-4">

        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    🔐 Integridade Blockchain
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        Cadeia de hash encadeada protegendo alterações de auditoria.
                    </p>

                    <div class="alert alert-success">
                        ✔ Sistema íntegro - sem violação detectada
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    🛰 Monitoramento SOC
                </div>
                <div class="card-body">

                    <ul class="list-unstyled">
                        <li>🟢 Firewall ativo</li>
                        <li>🟢 Fingerprint validado</li>
                        <li>🟡 Score de risco monitorado</li>
                        <li>🟢 Auditoria contínua</li>
                    </ul>

                </div>
            </div>
        </div>

    </div>

</div>

@endsection