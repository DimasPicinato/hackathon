<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('titulo', 'Presenza')</title>

        <link rel="stylesheet" href="/css/app.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Lexend:wght@500;600;700&family=Source+Sans+3:wght@300;400;500;600;700&display=swap">
    </head>
    <body class="auth">
        <div class="auth-wrap">
            <a class="brand brand-auth" href="{{ route('verificar.busca') }}">
                <span class="brand-mark">P</span> Presenza
            </a>
            <p class="brand-sub">presenças reais, certificados à prova</p>

            <div class="card auth-card">
                @yield('conteudo')
            </div>
        </div>
        @include('assistente.widget')
    </body>
</html>
