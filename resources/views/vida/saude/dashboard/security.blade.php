<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Security Dashboard</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        body {
            font-family: Arial;
            background: #0f172a;
            color: #e2e8f0;
            margin: 0;
            padding: 20px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }

        .card {
            background: #1e293b;
            padding: 15px;
            border-radius: 10px;
        }

        .danger { color: #ef4444; }
        .ok { color: #22c55e; }

        pre {
            font-size: 11px;
            overflow: auto;
        }
    </style>
</head>

<body>

<h2>🛡 Security Dashboard</h2>

<div class="grid">

    <div class="card">
        <h3>🔥 Firewall Hits</h3>
        <p>{{ $firewall_hits }}</p>
    </div>

    <div class="card">
        <h3>🛰 Global Lock</h3>
        <p class="{{ $firewall_lock ? 'danger' : 'ok' }}">
            {{ $firewall_lock ? 'ATIVO' : 'NORMAL' }}
        </p>
    </div>

    <div class="card">
        <h3>⛓ Audit Hash</h3>
        <p style="font-size:10px;">
            {{ $audit_hash }}
        </p>
    </div>

</div>

<br>

<div class="card">
    <h3>📜 Logs recentes</h3>
    <pre>
@foreach($recent_logs as $log)
{{ $log }}
@endforeach
    </pre>
</div>

</body>
</html>