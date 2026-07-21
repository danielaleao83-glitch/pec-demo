<!DOCTYPE html>
<html lang="pt-BR">
<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Muitas solicitações</title>

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{

            min-height:100vh;

            display:flex;
            justify-content:center;
            align-items:center;

            background:#0b1220;

            font-family: Arial, sans-serif;

            color:#e5e7eb;

            padding:20px;
        }

        .container{

            width:100%;
            max-width:460px;

            background:#111827;

            border:1px solid #1f2937;

            border-radius:16px;

            padding:40px 28px;

            text-align:center;

            box-shadow:0 0 40px rgba(0,0,0,.5);
        }

        h1{

            font-size:64px;

            color:#f97316;

            margin-bottom:10px;
        }

        h2{
            margin-bottom:14px;
        }

        p{

            color:#9ca3af;

            line-height:1.6;

            font-size:14px;
        }

        .btn{

            display:inline-block;

            margin-top:24px;

            padding:14px 20px;

            border-radius:12px;

            background:#2563eb;

            color:#fff;

            text-decoration:none;

            font-weight:600;
        }

        .btn:hover{
            opacity:.9;
        }

    </style>

</head>

<body>

    <div class="container">

        <h1>429</h1>

        <h2>Muitas solicitações</h2>

        <p>
            Seu acesso foi temporariamente limitado por segurança.
            Aguarde alguns instantes e tente novamente.
        </p>

        <a class="btn" href="{{ url()->previous() }}">
            Voltar
        </a>

    </div>

</body>
</html>
