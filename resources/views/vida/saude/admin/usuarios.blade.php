@extends('vida.saude.layouts.app')

@section('title', 'Gestão de Usuários')

@section('content')

<div class="container-fluid">

    {{-- 🔐 CABEÇALHO SOC --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>
                        <h4 class="mb-0">👨‍💻 Gestão de Usuários</h4>
                        <small class="text-muted">
                            Sistema hospitalar • Controle de acesso • Auditoria SOC
                        </small>
                    </div>

                    <div>
                        <a href="#" class="btn btn-primary">
                            + Novo Usuário
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- 📊 MÉTRICAS SOC --}}
    <div class="row mb-4">

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Total de Usuários</h6>
                    <h3>--</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Ativos</h6>
                    <h3 class="text-success">--</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Bloqueados SOC</h6>
                    <h3 class="text-danger">--</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Alertas Segurança</h6>
                    <h3 class="text-warning">--</h3>
                </div>
            </div>
        </div>

    </div>

    {{-- 🔐 ALERTA SOC (SE HOUVER ATAQUES) --}}
    <div class="alert alert-warning shadow-sm">
        ⚠ SOC ATIVO: Monitoramento de segurança em tempo real habilitado
    </div>

    {{-- 📋 TABELA DE USUÁRIOS --}}
    <div class="card shadow-sm border-0">

        <div class="card-header bg-white">
            <strong>Lista de Usuários</strong>
        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Perfil</th>
                            <th>Status</th>
                            <th>Último acesso</th>
                            <th>Segurança</th>
                            <th>Ações</th>
                        </tr>
                    </thead>

                    <tbody>

                        {{-- EXEMPLO (substituir por @foreach) --}}
                        <tr>
                            <td>#001</td>
                            <td>Administrador</td>
                            <td>admin@hospital.local</td>
                            <td>
                                <span class="badge bg-dark">ADMIN</span>
                            </td>
                            <td>
                                <span class="badge bg-success">ATIVO</span>
                            </td>
                            <td>Hoje</td>

                            {{-- 🔐 STATUS SOC --}}
                            <td>
                                <span class="badge bg-success">OK</span>
                            </td>

                            <td>
                                <button class="btn btn-sm btn-primary">Editar</button>
                                <button class="btn btn-sm btn-danger">Bloquear</button>
                            </td>
                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

@endsection