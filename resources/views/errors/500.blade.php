<!DOCTYPE html>
<html lang="pt-BR">
<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Erro interno</title>

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

            background:#0a0f1c;

            font-family: Arial, sans-serif;

            color:#f3f4f6;

            padding:20px;
        }

        .box{

            width:100%;
            max-width:480px;

            background:#111827;

            border-radius:16px;

            padding:42px 30px;

            text-align:center;

            border:1px solid #1f2937;
        }

        h1{

            font-size:66px;

            color:#ef4444;

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

            margin-top:26px;

            padding:14px 20px;

            border-radius:12px;

            background:#ef4444;

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

<div class="box">

    <h1>500</h1>

    <h2>Erro interno</h2>

    <p>
        O sistema encontrou uma falha inesperada.
        Nossa equipe técnica foi notificada.
    </p>

    <a class="btn" href="{{ url('/') }}">
        Voltar ao início
    </a>

</div>

</body>
</html>
