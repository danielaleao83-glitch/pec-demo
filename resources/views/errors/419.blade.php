<!DOCTYPE html>
<html lang="pt-BR">
<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Sessão expirada</title>

    <style>

        body{

            margin:0;

            min-height:100vh;

            display:flex;
            justify-content:center;
            align-items:center;

            background:#111827;

            color:#fff;

            font-family:
                Inter,
                Arial,
                sans-serif;

            padding:20px;
        }

        .card{

            width:100%;
            max-width:420px;

            background:#1f2937;

            border-radius:18px;

            padding:40px 30px;

            text-align:center;

            border:1px solid #374151;
        }

        h1{

            font-size:60px;

            color:#f59e0b;

            margin-bottom:15px;
        }

        p{

            color:#d1d5db;

            line-height:1.6;
        }

        a{

            display:inline-block;

            margin-top:25px;

            background:#f59e0b;

            color:#111827;

            text-decoration:none;

            padding:14px 20px;

            border-radius:12px;

            font-weight:700;
        }

    </style>

</head>

<body>

<div class="card">

    <h1>419</h1>

    <h2>Sessão expirada</h2>

    <p>
        Sua sessão expirou por segurança.
        Faça login novamente para continuar.
    </p>

    <a href="{{ route('login') }}">
        Fazer login
    </a>

</div>

</body>
</html>
