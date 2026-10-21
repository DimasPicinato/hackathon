@extends('layouts.portal')

@section('titulo', 'Reservas — Presenza')

@section('conteudo')
<p><a href="{{ route('portal') }}">← Voltar ao portal</a></p>
<h1>Reservas nominais <span class="muted">(RF05/RF08 — Líder/RH manda o time)</span></h1>
@if (session('ok'))<p class="flash-ok">{{ session('ok') }}</p>@endif
@if ($errors->any())<p class="flash-erros">{{ $errors->first() }}</p>@endif

<div class="card">
    <h2>Fazer reserva</h2>
    <form method="POST" action="{{ route('portal.rh.reservas.reservar') }}" class="form-inline">@csrf
        <select name="user_id" required>
            <option value="">Quem…</option>
            @foreach ($pessoas as $pessoa)
                <option value="{{ $pessoa->id }}">{{ $pessoa->name }} ({{ $pessoa->setor?->nome }})</option>
            @endforeach
        </select>
        <select name="aula_id" required>
            <option value="">Aula…</option>
            @foreach ($aulas as $aula)
                <option value="{{ $aula->id }}">
                    {{ $aula->treinamento->nome }} · {{ $aula->titulo }} — {{ $aula->data_inicio->format('d/m H:i') }}
                    ({{ $aula->vagasRestantes() }} vaga(s))
                </option>
            @endforeach
        </select>
        <button class="btn btn-primary btn-pequeno">Reservar</button>
    </form>
    <p class="muted">Aula lotada = entra automaticamente na fila de espera; cancelamento promove a fila (RF09).</p>
</div>

@foreach ($aulas as $aula)
    <div class="card">
        <h2>{{ $aula->treinamento->nome }} — {{ $aula->titulo }}
            <span class="muted">· {{ $aula->data_inicio->format('d/m H:i') }} · {{ $aula->vagasRestantes() }}/{{ $aula->vagas }} vaga(s)</span>
        </h2>
        <table class="table">
            @forelse ($aula->inscricoes->where('status', '!=', 'cancelada')->sortBy('posicao_fila') as $inscricao)
                <tr>
                    <td>{{ $inscricao->user->name }}</td>
                    <td><span class="badge badge-{{ $inscricao->status === 'reservada' ? 'manual' : 'fila' }}">
                        {{ $inscricao->status === 'reservada' ? 'reservada' : 'fila ' . $inscricao->posicao_fila }}
                    </span></td>
                    <td>
                        <form method="POST" action="{{ route('portal.rh.reservas.cancelar', $inscricao) }}">@csrf
                            <button class="btn btn-outline btn-pequeno"
                                    onclick="return confirm('Cancelar a vaga e promover a fila?')">
                                Cancelar
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td class="muted">Ninguém inscrito.</td></tr>
            @endforelse
        </table>
    </div>
@endforeach
@endsection
