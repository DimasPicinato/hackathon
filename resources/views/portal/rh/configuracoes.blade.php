@extends('layouts.portal')

@section('titulo', 'Configurações — Presenza')

@section('conteudo')
<a class="btn-voltar" href="{{ route('portal') }}"><x-icon nome="seta-esquerda"/> Voltar</a>
<h1>Configurações <span class="muted">(RF26 — Admin/RH)</span></h1>

@if ($errors->any())<p class="flash-erros">{{ $errors->first() }}</p>@endif

<div class="card">
    <h2>Catálogo de motivos de homologação <span class="muted">(RF27)</span></h2>
    <form method="POST" action="{{ route('portal.rh.configuracoes.motivo.criar') }}" class="form-inline">@csrf
        <input type="text" name="nome" placeholder="Novo motivo (ex.: Troca de turno de última hora)" maxlength="120" required>
        <button class="btn btn-primary btn-pequeno">Adicionar</button>
    </form>
    <table class="table">
        @foreach ($motivos as $motivo)
            <tr>
                <td>{{ $motivo->nome }}</td>
                <td><span class="badge badge-{{ $motivo->ativo ? 'aprovado' : 'recusado' }}">{{ $motivo->ativo ? 'ativo' : 'inativo' }}</span></td>
                <td>
                    <form method="POST" action="{{ route('portal.rh.configuracoes.motivo.alternar', $motivo) }}">@csrf
                        <button class="btn btn-outline btn-pequeno">{{ $motivo->ativo ? 'Desativar' : 'Reativar' }}</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </table>
</div>

<div class="card">
    <h2>Tipos de ticket <span class="muted">(RF10)</span></h2>
    <table class="table">
        @foreach ($tipos as $tipo)
            <tr>
                <td>{{ $tipo->nome }}</td>
                <td><span class="badge badge-{{ $tipo->ativo ? 'aprovado' : 'recusado' }}">{{ $tipo->ativo ? 'ativo' : 'inativo' }}</span></td>
                <td>
                    <form method="POST" action="{{ route('portal.rh.configuracoes.tipo.alternar', $tipo) }}">@csrf
                        <button class="btn btn-outline btn-pequeno">{{ $tipo->ativo ? 'Desativar' : 'Reativar' }}</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </table>
</div>

<div class="card">
    <h2>Termo LGPD — biometria facial <span class="muted">(RF04)</span></h2>
    <form method="POST" action="{{ route('portal.rh.configuracoes.termo.publicar') }}" class="form-inline-vertical">@csrf
        <input type="text" name="versao" placeholder="Versão (ex.: v2.0)" maxlength="20" required>
        <input type="text" name="titulo" placeholder="Título do termo" maxlength="120" required>
        <textarea name="conteudo" rows="4" placeholder="Texto completo do consentimento…" required></textarea>
        <button class="btn btn-accent btn-pequeno"
                onclick="return confirm('Publicar nova versão? TODAS as pessoas farão aceite novamente ao entrar no portal (RF04).')">
            Publicar nova versão
        </button>
    </form>
    <table class="table">
        @foreach ($termos as $termo)
            <tr>
                <td><strong>{{ $termo->titulo }}</strong> <span class="muted">({{ $termo->versao }})</span></td>
                <td><span class="badge badge-{{ $termo->ativo ? 'aprovado' : 'recusado' }}">{{ $termo->ativo ? 'vigente' : 'histórico' }}</span></td>
            </tr>
        @endforeach
    </table>
</div>

<div class="card">
    <h2>SMTP <span class="muted">(leitura)</span></h2>
    <table class="table">
        <tr><td>Mailer</td><td><code>{{ $email['mailer'] }}</code></td></tr>
        <tr><td>Host</td><td><code>{{ $email['host'] }}</code></td></tr>
        <tr><td>Porta</td><td><code>{{ $email['porta'] }}</code></td></tr>
        <tr><td>Remetente</td><td><code>{{ $email['nome'] }} &lt;{{ $email['remetente'] }}&gt;</code></td></tr>
    </table>
</div>
@endsection
