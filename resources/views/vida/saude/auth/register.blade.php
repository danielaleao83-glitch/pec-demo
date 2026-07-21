@extends('vida.saude.layouts.app')

@section('title', 'Cadastro Controlado')

@section('content')

<div class="container">

    <div class="row justify-content-center">

        <div class="col-md-6">

            <div class="card shadow">

                <div class="card-body">

                    <h4>🏥 Cadastro Hospitalar</h4>

                    <p class="text-muted">
                        Registro controlado com auditoria SOC ativa
                    </p>

                    <form method="POST" action="#">
                        @csrf

                        <input type="text"
                               class="form-control mb-3"
                               placeholder="Nome completo">

                        <input type="email"
                               class="form-control mb-3"
                               placeholder="E-mail institucional">

                        <input type="password"
                               class="form-control mb-3"
                               placeholder="Senha">

                        <button class="btn btn-dark w-100">
                            Solicitar acesso
                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection