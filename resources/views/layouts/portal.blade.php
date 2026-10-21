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
    <body>
        <header class="topbar">
            <a class="brand" href="{{ route('portal') }}"><span class="brand-mark">P</span> Presenza</a>
            <nav class="topbar-nav">
                <a href="{{ route('verificar.busca') }}" target="_blank">Verificar certificado</a>
            </nav>
            <div class="topbar-user">
                <span>{{ auth()->user()->name }} · {{ auth()->user()->matricula }}{{ auth()->user()->setor ? ' · ' . auth()->user()->setor->sigla : '' }}</span>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="btn btn-ghost-pequeno">Sair</button>
                </form>
            </div>
        </header>
        <main class="container">
            @if (session('status'))
                <div class="alert alert-ok">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-erro">
                    <ul style="margin:0;padding-left:1rem;">
                        @foreach ($errors->all() as $erro)
                            <li>{{ $erro }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @yield('conteudo')
        </main>
        <footer class="rodape">Presenza — Hackathon FATEC & ETEC · Usina Lins · CetaCeo</footer>
    </body>
</html>
