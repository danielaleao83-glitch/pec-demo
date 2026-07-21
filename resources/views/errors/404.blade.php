<!DOCTYPE html>
<html lang="pt-BR">
<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Página não encontrada</title>

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{

            background:#020617;

            color:#f8fafc;

            font-family:
                Inter,
                Arial,
                sans-serif;

            min-height:100vh;

            display:flex;
            justify-content:center;
            align-items:center;

            padding:20px;
        }

        .box{

            width:100%;
            max-width:500px;

            background:#0f172a;

            border-radius:18px;

            padding:45px 30px;

            text-align:center;

            border:1px solid #1e293b;
        }

        h1{

            font-size:72px;

            color:#38bdf8;

            margin-bottom:10px;
        }

        h2{

            margin-bottom:18px;
        }

        p{

            color:#94a3b8;

            line-height:1.6;
        }

        a{

            display:inline-block;

            margin-top:25px;

            background:#0ea5e9;

            color:#fff;

            padding:14px 22px;

            border-radius:12px;

            text-decoration:none;

            font-weight:600;
        }

    </style>

</head>

<body>

<div class="box">

    <h1>404</h1>

    <h2>Página não encontrada</h2>

    <p>
        O endereço solicitado não está disponível.
    </p>

    <a href="{{ url('/') }}">
        Retornar
    </a>

</div>

</body>
</html>
