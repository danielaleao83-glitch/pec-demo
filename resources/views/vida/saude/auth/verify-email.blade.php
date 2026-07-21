@extends('vida.saude.layouts.app')

@section('title', 'Verificação de E-mail')

@section('content')

<div class="container text-center">

    <div class="card shadow mt-5">

        <div class="card-body">

            <h4>📧 Verificação necessária</h4>

            <p class="text-muted">
                Confirme seu e-mail para continuar no sistema hospitalar
            </p>

            <form method="POST" action="#">
                @csrf

                <button class="btn btn-primary">
                    Reenviar e-mail
                </button>

            </form>

        </div>

    </div>

</div>

@endsection