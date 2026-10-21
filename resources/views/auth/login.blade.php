@extends('layouts.guest')

@section('titulo', 'Entrar — Presenza')

@section('conteudo')
    <h1>Entrar nos portais</h1>
    <p class="muted">Login/senha — a biometria aqui NÃO é usada (só para validar presença, Q10).</p>

    @if (session('status'))
        <div class="alert alert-ok">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <label for="email">E-mail corporativo</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
        @error('email') <p class="erro">{{ $message }}</p> @enderror

        <label for="password">Senha</label>
        <input id="password" type="password" name="password" required autocomplete="current-password">
        @error('password') <p class="erro">{{ $message }}</p> @enderror

        <label class="check">
            <input type="checkbox" name="remember"> Manter conectado
        </label>

        <div class="acoes-login">
            <a href="{{ route('password.request') }}">Esqueci minha senha</a>
            <button type="submit" class="btn btn-primary">Entrar</button>
        </div>
    </form>

    <div class="demo-creds">
        <strong>Contas da demo</strong>
        <p class="demo-senha">Senha única: <code>Password@123</code></p>
        <ul class="lista-credenciais">
            <li><span class="badge badge-facial">Admin</span><code>fernanda.carvalho.duarte</code></li>
            <li><span class="badge badge-fila">RH</span><code>isabela.cristina.fonseca</code></li>
            <li><span class="badge badge-manual">Instrutor</span><code>klaus.werner.steiner</code></li>
            <li><span class="badge badge-pendente">Líder</span><code>heloisa.fernandes.peixoto</code></li>
            <li><span class="badge badge-aprovado">Colaborador</span><code>otavio.martins.gomes</code></li>
        </ul>
        <p class="demo-dominio">E-mail completo = login acima <code>@usinalins.local</code></p>
    </div>
@endsection
