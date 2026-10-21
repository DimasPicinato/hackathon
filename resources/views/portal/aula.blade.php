@extends('layouts.portal')

@section('titulo', 'Chamada — '.$aula->treinamento->nome)

@php
    use App\Enums\StatusAula;
    $presenteIds = $presentes->pluck('user_id')->all();
@endphp

@section('conteudo')
<h1>{{ $aula->treinamento->nome }}</h1>
<p><strong>{{ $aula->titulo }}</strong>
    <span class="badge badge-{{ $aula->status->value }}">{{ $aula->status->value }}</span>
    · {{ $aula->data_inicio->format('d/m/Y H:i') }} · {{ $aula->local }}
    · instrutor: {{ $aula->instrutor?->name }}</p>

@if ($aula->status === StatusAula::Agendada)
    <div class="alert alert-erro">
        A aula ainda NÃO foi iniciada — a janela de presença facial só abre depois (RF03).
    </div>
    <form method="POST" action="{{ route('portal.aula.iniciar', $aula) }}">@csrf
        <button class="btn btn-primary">▶ Iniciar aula e abrir chamada</button>
    </form>
@elseif ($aula->status === StatusAula::EmAndamento)
    <div class="alert alert-ok">
        Aula EM ANDAMENTO desde {{ $aula->iniciada_em->format('H:i') }} — presenças faciais liberadas (RF03).
    </div>
@else
    <div class="alert alert-erro">
        Aula encerrada em {{ $aula->encerrada_em->format('H:i') }} — fora da janela, só homologação manual (RF03).
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
                    @if ($inscricao->status === 'lista_espera')
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
                                        <button class="btn btn-accent btn-pequeno">📷 Facial (fachada)</button>
                                    </form>
                                    <form method="POST" action="{{ route('portal.aula.presenca.manual', $aula) }}" class="form-manual">@csrf
                                        <input type="hidden" name="colaborador_id" value="{{ $pessoa->id }}">
                                        <label class="mini">Senha do próprio colaborador (RF15)</label>
                                        <input type="password" name="senha" required placeholder="senha">
                                        <label class="mini">Motivo do catálogo</label>
                                        <select name="motivo_id">
                                            <option value="">— escolher —</option>
                                            @foreach ($motivos as $motivo)
                                                <option value="{{ $motivo->id }}">{{ $motivo->nome }}</option>
                                            @endforeach
                                        </select>
                                        <label class="mini">…ou motivo novo no ato (RF27)</label>
                                        <input type="text" name="motivo_novo" placeholder="ex.: câmera com defeito aqui">
                                        <button class="btn btn-primary btn-pequeno">Homologar manualmente</button>
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
