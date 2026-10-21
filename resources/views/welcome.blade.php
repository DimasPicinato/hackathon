<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Presenza — presenças reais, certificados à prova</title>
        <link rel="stylesheet" href="/css/app.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Lexend:wght@500;600;700&family=Source+Sans+3:wght@300;400;500;600;700&display=swap">
    </head>
    <body>
        <header class="hero">
            <div class="hero-inner">
                <div class="brand brand-hero"><span class="brand-mark">P</span> Presenza</div>
                <h1>Presenças reais, certificados à prova.</h1>
                <p>
                    Gestão de treinamentos internos da Usina Lins com <strong>validação de presença
                    por reconhecimento facial</strong>, fila de espera automática, tickets e
                    certificados com <strong>verificação pública por QR/código</strong>.
                </p>
                <div class="hero-acoes">
                    <a class="btn btn-accent" href="{{ route('verificar.busca') }}">Verificar certificado</a>
                    <a class="btn btn-outline-h on-hero" href="{{ route('login') }}">Acessar portais</a>
                </div>
            </div>
        </header>
    </body>
</html>
