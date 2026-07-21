<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Acesso Restrito</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #0b1220;
            color: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .box {
            text-align: center;
            padding: 30px;
            border: 1px solid #1f2937;
            border-radius: 12px;
            background: #111827;
            max-width: 420px;
        }

        .danger {
            color: #ef4444;
            font-size: 18px;
            font-weight: bold;
        }

        .small {
            font-size: 12px;
            opacity: 0.7;
            margin-top: 10px;
        }
    </style>
</head>

<body>

<div class="box">

    <div class="danger">🚫 Acesso Bloqueado pelo Sistema de Segurança</div>

    <p>
        Sua atividade foi classificada como suspeita ou restrita.
    </p>

    <div class="small">
        Código de referência: {{ request()->header('X-Correlation-ID') ?? 'N/A' }}
    </div>

</div>

</body>
</html>