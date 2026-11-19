@extends('layouts.guest')

@section('titulo', 'Nova senha — Presenza')

@section('conteudo')
<h1>Nova senha</h1>
<p class="muted">Defina a sua nova senha de acesso.</p>

<form method="POST" action="{{ route('password.store') }}">
    @csrf

    <input type="hidden" name="token" value="{{ $request->route('token') }}">

    <label for="email">E-mail</label>
    <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">
    @error('email') <p class="erro">{{ $message }}</p> @enderror

    <label for="password">Nova senha</label>
    <input id="password" type="password" name="password" required autocomplete="new-password">
    @error('password') <p class="erro">{{ $message }}</p> @enderror

    <label for="password_confirmation">Confirmar nova senha</label>
    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
    @error('password_confirmation') <p class="erro">{{ $message }}</p> @enderror

    <div class="acoes-login">
        <button type="submit" class="btn btn-primary">Redefinir senha</button>
    </div>
</form>
@endsection