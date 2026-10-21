<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Verificação — {{ $codigo }}</title>
        <link rel="stylesheet" href="/css/app.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Lexend:wght@500;600;700&family=Source+Sans+3:wght@300;400;500;600;700&display=swap">
    </head>
    <body>
        <div class="auth-wrap">
            <a class="brand brand-auth" href="/"><span class="brand-mark">P</span> Presenza</a>
            <p class="brand-sub">verificação pública de certificados — sem login</p>

            <div class="card auth-card">
                @if (! $certificado)
                    <h1 class="resultado resultado-nao-encontrado">NÃO ENCONTRADO</h1>
                    <p class="muted">Não existe certificado com o código <code>{{ $codigo }}</code>.</p>
                    <a class="btn btn-outline" href="{{ route('verificar.busca') }}">Verificar outro</a>
                @else
                    <h1 class="resultado resultado-{{ $resultado->value }}">
                        @if ($resultado->value === 'valido') VÁLIDO
                        @elseif ($resultado->value === 'expirado') EXPIRADO
                        @else NÃO ENCONTRADO
                        @endif
                    </h1>
                    <p class="muted">Resultado para o código <code>{{ $codigo }}</code>.</p>

                    <dl class="verif-dados">
                        <dt>Nome</dt><dd>{{ $certificado->user->name }}</dd>
                        <dt>Treinamento</dt><dd>{{ $certificado->treinamento->nome }}</dd>
                        <dt>Carga horária</dt><dd>{{ $certificado->carga_horaria ?? $certificado->treinamento->carga_horaria }} h</dd>
                        <dt>Conclusão</dt><dd>{{ $certificado->concluido_em?->format('d/m/Y') ?? '—' }}</dd>
                        <dt>Válido até</dt><dd>{{ $certificado->valido_ate?->format('d/m/Y') ?? '—' }}</dd>
                        <dt>Instrutor(es)</dt><dd>{{ $instrutores ?: '—' }}</dd>
                    </dl>

                    @if ($resultado->value !== 'expirado' && $qr)
                        <img class="qr" src="{{ $qr }}" alt="QR do código {{ $codigo }}" width="140" height="140">
                    @endif

                    <div class="hero-acoes">
                        @if ($resultado->value === 'expirado')
                            <a class="btn btn-outline" href="{{ route('verificar.busca') }}">Verificar outro</a>
                        @else
                            <a class="btn btn-primary" href="{{ route('verificar.pdf', $codigo) }}">Baixar PDF</a>
                            <a class="btn btn-outline" href="{{ route('verificar.busca') }}">Verificar outro</a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </body>
</html>
