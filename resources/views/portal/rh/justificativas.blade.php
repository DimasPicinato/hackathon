@extends('layouts.portal')

@section('titulo', 'Justificativas — Presenza')

@section('conteudo')
<p><a href="{{ route('portal') }}">← Voltar ao portal</a></p>
<h1>Justificativas de faltas <span class="muted">(RF12–RF13)</span></h1>
@if (session('ok'))<p class="flash-ok">{{ session('ok') }}</p>@endif

<div class="card">
    <h2>Registro direto pela gestão (RF13 — sem ticket, a qualquer momento)</h2>
    <form method="POST" action="{{ route('portal.rh.justificativas.registrar') }}" class="form-inline">@csrf
        <select name="user_id" required>
            <option value="">Colaborador…</option>
            @foreach (\App\Models\User::query()->where('ativo', true)->whereJsonContains('papeis', 'colaborador')->orderBy('name')->get() as $pessoa)
                <option value="{{ $pessoa->id }}">{{ $pessoa->name }} ({{ $pessoa->setor?->nome }})</option>
            @endforeach
        </select>
        <select name="aula_id" required>
            <option value="">Aula…</option>
            @foreach (\App\Models\Aula::with('treinamento')->orderByDesc('data_inicio')->get() as $aula)
                <option value="{{ $aula->id }}">{{ $aula->treinamento->nome }} · {{ $aula->titulo }} ({{ $aula->data_inicio->format('d/m') }})</option>
            @endforeach
        </select>
        <select name="status">
            <option value="pendente">pendente</option>
            <option value="aprovado">aprovado</option>
            <option value="recusado">recusado</option>
        </select>
        <input type="text" name="texto" placeholder="Motivo (ex.: atestado)" maxlength="1000">
        <button class="btn btn-primary btn-pequeno">Registrar</button>
    </form>
</div>

@forelse ($justificativas as $just)
    <div class="card">
        <div class="linha-aula">
            <div>
                <strong>{{ $just->user->name }}</strong> <span class="muted">({{ $just->user->setor?->nome }})</span><br>
                <span class="muted">{{ $just->aula->treinamento->nome }} — {{ $just->aula->titulo }} · {{ $just->aula->data_inicio->format('d/m/Y') }}</span><br>
                @if ($just->texto)<em>“{{ $just->texto }}”</em> @endif
                @if ($just->ticket_id)
                    <span class="badge badge-fila">via ticket #{{ $just->ticket_id }}</span>
                @else
                    <span class="badge badge-manual">registro direto (RF13)</span>
                @endif
                @if ($just->alteradoPor)
                    <div class="muted">Última alteração: {{ $just->alteradoPor->name }} · {{ $just->alterado_em?->format('d/m H:i') }}</div>
                @endif
            </div>
            <div class="linha-acoes">
                <span class="badge badge-{{ $just->status->value }}">{{ $just->status->value }}</span>
                <form method="POST" action="{{ route('portal.rh.justificativas.status', $just) }}">@csrf
                    <input type="hidden" name="status" value="aprovado">
                    <button class="btn btn-primary btn-pequeno">✓ Aprovar</button>
                </form>
                <form method="POST" action="{{ route('portal.rh.justificativas.status', $just) }}">@csrf
                    <input type="hidden" name="status" value="recusado">
                    <button class="btn btn-outline btn-pequeno">✗ Recusar</button>
                </form>
            </div>
        </div>
    </div>
@empty
    <p class="muted">Nenhuma justificativa.</p>
@endforelse
@endsection
