@extends('layouts.portal')

@section('titulo', 'Meu perfil — Presenza')

@section('conteudo')
<a class="btn-voltar" href="{{ route('portal') }}"><x-icon nome="seta-esquerda"/> Voltar</a>
<h1>Meu perfil</h1>
<p class="pagina-sub">Atualize seus dados de acesso e a sua senha. A exclusão da conta é definitiva e não pode ser desfeita.</p>

<div class="card">
    <h2>Dados pessoais</h2>
    @include('profile.partials.update-profile-information-form')
</div>

<div class="card">
    <h2>Alterar senha</h2>
    @include('profile.partials.update-password-form')
</div>

<div class="card">
    <h2>Excluir conta</h2>
    @include('profile.partials.delete-user-form')
</div>
@endsection