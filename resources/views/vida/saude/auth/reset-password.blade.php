@extends('vida.saude.layouts.app')

@section('title', 'Reset de Senha')

@section('content')

<div class="container">

    <div class="row justify-content-center">

        <div class="col-md-5">

            <div class="card shadow">

                <div class="card-body">

                    <h5>🔐 Nova Senha</h5>

                    <form method="POST" action="#">
                        @csrf

                        <input type="password"
                               class="form-control mb-3"
                               placeholder="Nova senha">

                        <input type="password"
                               class="form-control mb-3"
                               placeholder="Confirmar senha">

                        <button class="btn btn-success w-100">
                            Atualizar senha
                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection