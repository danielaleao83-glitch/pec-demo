@extends('vida.saude.layouts.app')

@section('title', 'Login - Vida/Saúde')

@section('content')

<div style="
    min-height:80vh;
    display:flex;
    align-items:center;
    justify-content:center;
">

    <div style="
        width:100%;
        max-width:420px;
        background:#111827;
        border:1px solid rgba(255,255,255,.08);
        border-radius:18px;
        padding:40px;
        box-shadow:0 20px 40px rgba(0,0,0,.4);
    ">

        {{-- LOGO --}}
        <div style="text-align:center;margin-bottom:30px;">

            <div style="
                width:72px;
                height:72px;
                margin:auto;
                border-radius:20px;
                background:#1976d2;
                display:flex;
                align-items:center;
                justify-content:center;
                font-size:28px;
                font-weight:bold;
                color:white;
            ">
                VS
            </div>

            <h1 style="
                color:white;
                margin-top:20px;
                margin-bottom:5px;
                font-size:28px;
            ">
                Vida/Saúde
            </h1>

            <p style="
                color:#93c5fd;
                opacity:.8;
                margin:0;
            ">
                Prontuário Eletrônico
            </p>

        </div>

        {{-- ERROS --}}
        @if ($errors->any())

            <div style="
                background:#7f1d1d;
                color:#fecaca;
                padding:12px;
                border-radius:10px;
                margin-bottom:20px;
                font-size:14px;
            ">

                {{ $errors->first() }}

            </div>

        @endif

        {{-- FORM --}}
        <form method="POST" action="{{ route('login.post') }}">

            @csrf

            <div style="margin-bottom:18px;">

                <label style="
                    display:block;
                    margin-bottom:8px;
                    color:#cbd5e1;
                    font-size:14px;
                ">
                    E-mail
                </label>

                <input
                    type="email"
                    name="email"
                    required
                    autocomplete="email"

                    style="
                        width:100%;
                        padding:14px;
                        border-radius:12px;
                        border:1px solid #374151;
                        background:#0f172a;
                        color:white;
                        outline:none;
                        box-sizing:border-box;
                    "
                >

            </div>

            <div style="margin-bottom:25px;">

                <label style="
                    display:block;
                    margin-bottom:8px;
                    color:#cbd5e1;
                    font-size:14px;
                ">
                    Senha
                </label>

                <input
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"

                    style="
                        width:100%;
                        padding:14px;
                        border-radius:12px;
                        border:1px solid #374151;
                        background:#0f172a;
                        color:white;
                        outline:none;
                        box-sizing:border-box;
                    "
                >

            </div>

            <button
                type="submit"

                style="
                    width:100%;
                    background:#1976d2;
                    color:white;
                    border:none;
                    padding:14px;
                    border-radius:12px;
                    font-size:15px;
                    font-weight:bold;
                    cursor:pointer;
                "
            >
                Entrar no Sistema
            </button>

        </form>

        {{-- FOOTER --}}
        <div style="
            margin-top:25px;
            text-align:center;
            color:#64748b;
            font-size:12px;
        ">

            🛰 SOC ACTIVE • Ambiente Seguro

        </div>

    </div>

</div>

@endsection