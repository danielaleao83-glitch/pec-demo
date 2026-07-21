<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">

    <title>Verificação de Conta</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>

        body{
            margin:0;
            padding:0;
            background:#f4f7fa;
            font-family:Arial, Helvetica, sans-serif;
            color:#1f2937;
        }

        .container{
            width:100%;
            max-width:620px;
            margin:40px auto;
            background:#ffffff;
            border-radius:12px;
            overflow:hidden;
            border:1px solid #e5e7eb;
        }

        .header{
            background:#111827;
            padding:32px;
            text-align:center;
        }

        .header h1{
            margin:0;
            color:#ffffff;
            font-size:22px;
        }

        .content{
            padding:40px;
        }

        .content p{
            font-size:15px;
            line-height:1.7;
            color:#374151;
        }

        .button-area{
            margin:35px 0;
            text-align:center;
        }

        .button{
            display:inline-block;
            padding:14px 28px;
            background:#059669;
            color:#ffffff !important;
            text-decoration:none;
            border-radius:8px;
            font-weight:bold;
            font-size:15px;
        }

        .notice{
            margin-top:30px;
            background:#f9fafb;
            padding:18px;
            border-left:4px solid #059669;
            border-radius:6px;
        }

        .footer{
            padding:24px;
            background:#f3f4f6;
            text-align:center;
            font-size:12px;
            color:#6b7280;
        }

        .token{
            word-break:break-all;
            font-size:12px;
            color:#6b7280;
        }

        @media only screen and (max-width:600px){

            .content{
                padding:24px;
            }

            .header{
                padding:24px;
            }
        }

    </style>
</head>
<body>

<div class="container">

    <div class="header">
        <h1>Validação de Conta</h1>
    </div>

    <div class="content">

        <p>
            Olá,
        </p>

        <p>
            Para ativar sua conta com segurança, confirme seu endereço de e-mail.
        </p>

        <div class="button-area">

            <a
                href="{{ $url }}"
                class="button"
                target="_blank"
                rel="noopener noreferrer"
            >
                Verificar Conta
            </a>

        </div>

        <div class="notice">

            <strong>Segurança da conta:</strong>

            <ul>
                <li>O link possui assinatura criptográfica;</li>
                <li>A validação possui tempo limitado;</li>
                <li>Tentativas inválidas são registradas;</li>
                <li>O acesso é monitorado por auditoria.</li>
            </ul>

        </div>

        <p>
            Caso o botão não funcione:
        </p>

        <p class="token">
            {{ $url }}
        </p>

    </div>

    <div class="footer">

        <p>
            Mensagem automática de autenticação.
        </p>

        <p>
            © {{ date('Y') }} Plataforma Clínica
        </p>

    </div>

</div>

</body>
</html>