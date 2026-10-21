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
        @php
            $u = auth()->user();
            $ehRhOrAdmin = $u->temPapel('rh') || $u->temPapel('admin');
            $ehLider = $u->temPapel('lider');
            $botaoAtivo = fn (string|array $padrao): string => request()->routeIs($padrao) ? 'ativo' : '';
        @endphp
        <a class="sr-only atalho-conteudo" href="#conteudo">Pular para o conteúdo</a>
        <div class="casca">
            <aside class="lateral">
                <a class="brand" href="{{ route('portal') }}"><span class="brand-mark">P</span> Presenza</a>
                <nav class="lateral-nav" aria-label="Navegação principal">
                    <a class="lateral-link {{ $botaoAtivo('portal') }}" href="{{ route('portal') }}">
                        <x-icon nome="inicio"/> Início
                    </a>

                    @if ($ehRhOrAdmin || $ehLider)
                        <span class="lateral-grupo-titulo">Gestão</span>
                        @if ($ehRhOrAdmin)
                            <a class="lateral-link {{ $botaoAtivo('portal.rh.tickets') }}" href="{{ route('portal.rh.tickets') }}">
                                <x-icon nome="tickets"/> Tickets
                            </a>
                        @endif
                        <a class="lateral-link {{ $botaoAtivo('portal.rh.justificativas') }}" href="{{ route('portal.rh.justificativas') }}">
                            <x-icon nome="justificativas"/> Justificativas
                        </a>
                        <a class="lateral-link {{ $botaoAtivo('portal.rh.reservas') }}" href="{{ route('portal.rh.reservas') }}">
                            <x-icon nome="reservas"/> Reservas do time
                        </a>
                        <a class="lateral-link {{ $botaoAtivo('portal.rh.treinamentos') }}" href="{{ route('portal.rh.treinamentos') }}">
                            <x-icon nome="treinamentos"/> Treinamentos
                        </a>
                        <a class="lateral-link {{ $botaoAtivo('portal.rh.certificados') }}" href="{{ route('portal.rh.certificados') }}">
                            <x-icon nome="certificados"/> Certificados
                        </a>
                        <a class="lateral-link {{ $botaoAtivo(['portal.rh.relatorios', 'portal.rh.relatorios.*']) }}" href="{{ route('portal.rh.relatorios') }}">
                            <x-icon nome="relatorios"/> Relatórios
                        </a>
                        <a class="lateral-link {{ $botaoAtivo('portal.rh.conformidade') }}" href="{{ route('portal.rh.conformidade') }}">
                            <x-icon nome="conformidade"/> Matriz de conformidade
                        </a>
                        @if ($ehRhOrAdmin)
                            <a class="lateral-link {{ $botaoAtivo('portal.rh.usuarios') }}" href="{{ route('portal.rh.usuarios') }}">
                                <x-icon nome="usuarios"/> Usuários
                            </a>
                            <a class="lateral-link {{ $botaoAtivo('portal.rh.configuracoes') }}" href="{{ route('portal.rh.configuracoes') }}">
                                <x-icon nome="configuracoes"/> Configurações
                            </a>
                            @if ($u->temPapel('admin'))
                                <a class="lateral-link {{ $botaoAtivo('portal.rh.assistente.conversas') }}" href="{{ route('portal.rh.assistente.conversas') }}">
                                    <x-icon nome="tickets"/> Conversas do assistente
                                </a>
                            @endif
                        @endif
                    @endif
                </nav>

                <div class="lateral-pe">
                    <a class="lateral-link {{ $botaoAtivo('profile.*') }}" href="{{ route('profile.edit') }}">
                        <x-icon nome="perfil"/> Meu perfil
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="lateral-link">
                            <x-icon nome="sair"/> Sair
                        </button>
                    </form>
                </div>
            </aside>

            <div class="casca-conteudo">
                <header class="topbar">
                    <nav class="topbar-nav">
                        <a href="{{ route('verificar.busca') }}" target="_blank">Verificar certificado (público)</a>
                    </nav>
                    <div class="topbar-user">
                        <span><strong>{{ $u->name }}</strong><br>{{ $u->matricula }}{{ $u->setor ? ' · ' . $u->setor->sigla : '' }}</span>
                    </div>
                </header>
                <main class="container" id="conteudo">
                    @if (session('status'))
                        <div class="alert alert-ok">{{ session('status') }}</div>
                    @endif
                    @if (session('ok'))
                        <div class="flash-ok">{{ session('ok') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="flash-erros">
                            <ul style="margin:0;">
                                @foreach ($errors->all() as $erro)
                                    <li>{{ $erro }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @yield('conteudo')
                </main>
                <footer class="rodape">Presenza — Hackathon FATEC & ETEC · Usina Lins · CetaCeo</footer>
            </div>
        </div>
        <script>
            // Busca client-side para listas/tabelas: input[data-busca="id-do-alvo"]
            document.querySelectorAll('input[data-busca]').forEach(function (campo) {
                var alvo = document.getElementById(campo.dataset.busca);
                if (!alvo) return;
                campo.addEventListener('input', function () {
                    var consulta = campo.value.trim().toLowerCase();
                    var linhas = alvo.tagName === 'TABLE'
                        ? alvo.querySelectorAll('tbody tr')
                        : alvo.querySelectorAll('[data-linha]');
                    linhas.forEach(function (linha) {
                        var mostra = !consulta || linha.textContent.toLowerCase().indexOf(consulta) !== -1;
                        linha.style.display = mostra ? '' : 'none';
                    });
                });
            });
        </script>
    </body>
</html>
