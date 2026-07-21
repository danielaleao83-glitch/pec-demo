```blade
<!DOCTYPE html>
<html lang="pt-BR">
<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Acesso Negado</title>

    <meta
        http-equiv="X-UA-Compatible"
        content="IE=edge"
    >

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{

            min-height:100vh;

            display:flex;
            align-items:center;
            justify-content:center;

            background:#0f172a;

            font-family:
                Inter,
                Arial,
                sans-serif;

            color:#e2e8f0;

            padding:20px;
        }

        .container{

            width:100%;
            max-width:460px;

            background:#111827;

            border:1px solid #1e293b;

            border-radius:18px;

            padding:40px 30px;

            text-align:center;

            box-shadow:
                0 0 40px rgba(0,0,0,.45);
        }

        h1{

            font-size:72px;

            color:#ef4444;

            margin-bottom:10px;
        }

        h2{

            font-size:22px;

            margin-bottom:18px;
        }

        p{

            color:#94a3b8;

            line-height:1.6;

            font-size:15px;
        }

        a{

            display:inline-block;

            margin-top:28px;

            padding:14px 22px;

            border-radius:12px;

            text-decoration:none;

            background:#2563eb;

            color:#fff;

            font-weight:600;

            transition:.2s;
        }

        a:hover{
            opacity:.9;
        }

        @media(max-width:600px){

            h1{
                font-size:56px;
            }

            .container{
                padding:32px 24px;
            }
        }

    </style>

</head>

<body>

    <div class="container">

        <h1>403</h1>

        <h2>Acesso negado</h2>

        <p>
            Você não possui permissão para acessar este recurso.
        </p>

        <a href="{{ url('/') }}">
            Voltar ao painel
        </a>

    </div>

</body>
</html>
```
