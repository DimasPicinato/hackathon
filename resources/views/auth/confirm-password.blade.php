@extends('layouts.guest')

@section('titulo', 'Confirmar senha — Presenza')

@section('conteudo')
<h1>Confirmar senha</h1>
<p class="muted">Esta é uma área protegida. Confirme a sua senha para continuar.</p>

<form method="POST" action="{{ route('password.confirm') }}">
    @csrf

    <label for="password">Senha</label>
    <input id="password" type="password" name="password" required autocomplete="current-password">
    @error('password') <p class="erro">{{ $message }}</p> @enderror

    <div class="acoes-login">
        <button type="submit" class="btn btn-primary">Confirmar</button>
    </div>
</form>
@endsection