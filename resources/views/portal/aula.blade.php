@extends('layouts.portal')

@section('titulo', 'Chamada — '.$aula->treinamento->nome)

@php
    use App\Enums\StatusAula;
    $presenteIds = $presentes->pluck('user_id')->all();
@endphp

@section('conteudo')
<a class="btn-voltar" href="{{ route('portal') }}"><x-icon nome="seta-esquerda"/> Voltar</a>
<h1>{{ $aula->treinamento->nome }}</h1>
<p><strong>{{ $aula->titulo }}</strong>
    <span class="badge badge-{{ $aula->status->value }}">{{ $aula->status->value }}</span>
    · {{ $aula->data_inicio->format('d/m/Y H:i') }} · {{ $aula->local }}
    · instrutor: {{ $aula->instrutor?->name }}</p>

    <div class="alert alert-erro">
        A aula ainda não foi iniciada — a validação de presença abre quando o instrutor iniciar a aula.
    </div>
    <form method="POST" action="{{ route('portal.aula.iniciar', $aula) }}">@csrf
        <button class="btn btn-primary">▶ Iniciar aula e abrir chamada</button>
    </form>
@elseif ($aula->status === StatusAula::EmAndamento)
    <div class="alert alert-ok">
        Aula em andamento desde {{ $aula->iniciada_em->format('H:i') }} — validação de presença liberada.
    </div>
@else
    <div class="alert alert-erro">
        Aula encerrada em {{ $aula->encerrada_em->format('H:i') }} — fora da janela, só é possível registrar presença manualmente, com senha da pessoa e motivo.
    </div>
@endif

<div class="card">
    <h2>Convocados e presenças</h2>
    <table class="table">
        <thead><tr><th>Pessoa</th><th>Vaga</th><th>Presença</th><th>Ações</th></tr></thead>
        @forelse ($inscritos as $inscricao)
            @php $pessoa = $inscricao->user; @endphp
            <tr>
                <td><strong>{{ $pessoa->name }}</strong><br><span class="muted">{{ $pessoa->matricula }}</span></td>
                <td>
                    @if ($inscricao->status === 'cancelada')
                        <span class="badge badge-cancelada">cancelada</span>
                    @elseif ($inscricao->status === 'lista_espera')
                        <span class="badge badge-fila">fila · pos. {{ $inscricao->posicao_fila }}</span>
                    @elseif ($inscricao->status === 'reservada')
                        <span class="badge badge-manual">nominal</span>
                    @else
                        <span class="badge badge-recusado">{{ $inscricao->status }}</span>
                    @endif
                </td>
                <td>
                    @php $presenca = $presentes->firstWhere('user_id', $pessoa->id); @endphp
                    @if ($presenca)
                        <span class="badge badge-{{ $presenca->metodo->value }}">
                            {{ $presenca->metodo->value }}@if($presenca->motivo) · {{ $presenca->motivo->nome }}@endif
                        </span>
                    @else
                        <span class="muted">—</span>
                    @endif
                </td>
                <td>
                    @unless (in_array($pessoa->id, $presenteIds))
                        @if ($aula->status === StatusAula::EmAndamento)
                            <details>
                                <summary class="btn btn-outline btn-pequeno">Registrar presença ▾</summary>
                                <div class="acao-chamada">
                                    <form method="POST" action="{{ route('portal.aula.presenca.facial', $aula) }}" class="inline">@csrf
                                        <input type="hidden" name="colaborador_id" value="{{ $pessoa->id }}">
                                        <button class="btn btn-accent btn-pequeno">📷 Reconhecimento facial</button>
                                    </form>
                                    <form method="POST" action="{{ route('portal.aula.presenca.manual', $aula) }}" class="form-manual">@csrf
                                        <input type="hidden" name="colaborador_id" value="{{ $pessoa->id }}">
                                        <label class="mini">Senha da própria pessoa (confirma a presença — antifraude)</label>
                                        <input type="password" name="senha" required placeholder="senha">
                                        <label class="mini">Motivo do catálogo</label>
                                        <select name="motivo_id">
                                            <option value="">— escolher —</option>
                                            @foreach ($motivos as $motivo)
                                                <option value="{{ $motivo->id }}">{{ $motivo->nome }}</option>
                                            @endforeach
                                        </select>
                                        <label class="mini">…ou registre um motivo novo</label>
                                        <input type="text" name="motivo_novo" placeholder="ex.: câmera com defeito aqui">
                                        <button class="btn btn-primary btn-pequeno">Registrar presença manual</button>
                                    </form>
                                </div>
                            </details>
                        @endif
                    @endunless
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="muted">Ninguém inscrito nesta aula.</td></tr>
        @endforelse
    </table>
</div>

<div class="card">
    <h2>Presenças registradas ({{ $presentes->count() }})</h2>
    <table class="table">
        @forelse ($presentes as $presenca)
            <tr>
                <td>{{ $presenca->user->name }}</td>
                <td><span class="badge badge-{{ $presenca->metodo->value }}">{{ $presenca->metodo->value }}</span></td>
                <td class="muted">{{ $presenca->motivo?->nome }} · {{ $presenca->created_at->format('H:i') }}</td>
            </tr>
        @empty
            <tr><td class="muted">Nenhuma ainda.</td></tr>
        @endforelse
    </table>
</div>
@endsection
