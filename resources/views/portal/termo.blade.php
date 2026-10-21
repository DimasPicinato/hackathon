@extends('layouts.portal')

@section('titulo', 'Aceite do termo LGPD — Presenza')

@section('conteudo')
<div class="card termo-card">
    <h1>Termo de Consentimento — Biometria Facial</h1>
    <p class="muted">Versão {{ $termo->versao }} · obrigatório para presença facial (RF04). Sem aceite, a
        presença continua possível por homologação manual com o instrutor.</p>

    <div class="termo-conteudo">{{ $termo->conteudo }}</div>

    <form method="POST" action="{{ route('termo.aceitar') }}">
        @csrf
        <label class="check">
            <input type="checkbox" name="concordo" value="1" required>
            Li e concordo com o uso da biometria <strong>exclusivamente para validação de presença</strong>.
        </label>
        <button type="submit" class="btn btn-primary">De acordo — acessar os portais</button>
    </form>
</div>
@endsection
