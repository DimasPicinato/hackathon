@extends('layouts.guest')

@section('titulo', 'Verificar e-mail — Presenza')

@section('conteudo')
<h1>Verificar e-mail</h1>
<p class="muted">Antes de começar, confirme o seu endereço de e-mail clicando no link que acabamos de enviar. Se não recebeu, podemos enviar outro.</p>

@if (session('status') == 'verification-link-sent')
    <div class="alert alert-ok">Um novo link de confirmação foi enviado para o seu e-mail.</div>
@endif

<form method="POST" action="{{ route('verification.send') }}">
    @csrf
    <div class="acoes-login">
        <button type="submit" class="btn btn-primary">Reenviar e-mail de confirmação</button>
    </div>
</form>

<form method="POST" action="{{ route('logout') }}" style="margin-top:var(--space-3);">
    @csrf
    <button type="submit" class="btn btn-outline">Sair</button>
</form>
@endsection