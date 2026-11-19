@extends('layouts.guest')

@section('titulo', 'Esqueci minha senha — Presenza')

@section('conteudo')
<h1>Esqueci minha senha</h1>
<p class="muted">Informe o seu e-mail corporativo e enviaremos um link para você criar uma nova senha.</p>

@if (session('status'))
    <div class="alert alert-ok">{{ session('status') }}</div>
@endif

<form method="POST" action="{{ route('password.email') }}">
    @csrf

    <label for="email">E-mail corporativo</label>
    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
    @error('email') <p class="erro">{{ $message }}</p> @enderror

    <div class="acoes-login">
        <a href="{{ route('login') }}">Voltar ao login</a>
        <button type="submit" class="btn btn-primary">Enviar link de redefinição</button>
    </div>
</form>
@endsection