<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Verificar certificado — Presenza</title>
        <link rel="stylesheet" href="/css/app.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Lexend:wght@500;600;700&family=Source+Sans+3:wght@300;400;500;600;700&display=swap">
    </head>
    <body>
        <div class="auth-wrap">
            <a class="brand brand-auth" href="/"><span class="brand-mark">P</span> Presenza</a>
            <p class="brand-sub">verificação pública de certificados — sem login</p>

            <div class="card auth-card">
                <h1>Verificar certificado</h1>
                <p class="muted">Insira o código impresso no certificado ou escaneie o QR.</p>

                <form method="GET" action="{{ route('verificar.busca') }}" class="form-vertical">
                    <label for="q">Código do certificado</label>
                    <input id="q" name="q" placeholder="ex.: PZ-2025-000101" required autofocus>
                    <button class="btn btn-primary btn-bloco" type="submit">Verificar certificado</button>
                </form>
            </div>
        </div>
    </body>
</html>
