@extends('vida.saude.layouts.app')

@section('title', 'Recuperação de Senha')

@section('content')

<div class="container">

    <div class="row justify-content-center">

        <div class="col-md-5">

            <div class="card shadow">

                <div class="card-body">

                    <h5>🔑 Recuperar Acesso</h5>

                    <p class="text-muted">
                        Sistema seguro — solicitação será auditada
                    </p>

                    <form method="POST" action="#">
                        @csrf

                        <input type="email"
                               class="form-control mb-3"
                               placeholder="Seu e-mail institucional">

                        <button class="btn btn-warning w-100">
                            Enviar link
                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection