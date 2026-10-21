@extends('layouts.portal')

@section('titulo', 'Tickets — Presenza')

@section('conteudo')
<a class="btn-voltar" href="{{ route('portal') }}"><x-icon nome="seta-esquerda"/> Voltar</a>
<h1>Tickets <span class="muted">(RF10–RF12)</span></h1>


<div class="card">
    <div class="cabeca-tabela">
        <h2>Fila de trabalho</h2>
        <input type="search" class="campo-busca" data-busca="lista-tickets" placeholder="Buscar por nº, tipo, pessoa…" aria-label="Buscar tickets">
    </div>
    <div id="lista-tickets">
@forelse ($tickets as $ticket)
    <div class="card" data-linha>
        <div class="linha-aula">
            <div>
                <strong>#{{ $ticket->id }} · {{ $ticket->tipoTicket->nome }}</strong>
                <span class="badge badge-{{ $ticket->status->value }}">{{ $ticket->status->value }}</span><br>
                <strong>{{ $ticket->user->name }}</strong> <span class="muted">({{ $ticket->user->matricula }} · {{ $ticket->user->setor?->nome }})</span><br>
                @if ($ticket->treinamento)
                    <span class="muted">Treinamento: {{ $ticket->treinamento->nome }}</span><br>
                @endif
                @if ($ticket->texto)
                    <em>“{{ $ticket->texto }}”</em><br>
                @endif
                @if ($ticket->status->value === 'resolvido')
                    <span class="muted">Resolvido por {{ $ticket->resolvidoPor?->name }} · {{ $ticket->resolvido_em?->format('d/m H:i') }} — {{ $ticket->resultado }}</span>
                @endif
            </div>

            @if ($ticket->status->value === 'aberto')
                <div class="linha-acoes">
                    @if ($ticket->tipoTicket->nome === 'Cancelar vaga')
                        <form method="POST" action="{{ route('portal.rh.tickets.resolver', $ticket) }}">@csrf
                            <input type="hidden" name="acao" value="cancelar_vaga">
                            <button class="btn btn-accent btn-pequeno"
                                    onclick="return confirm('Devolver as vagas em TODAS as aulas e promover a fila?')">
                                Cancelar vagas + promover fila (RF09)
                            </button>
                        </form>
                    @elseif ($ticket->tipoTicket->nome === 'Justificar falta')
                        <form method="POST" action="{{ route('portal.rh.tickets.resolver', $ticket) }}" class="form-inline">@csrf
                            <input type="hidden" name="acao" value="justificar_falta">
                            <select name="aula_id" required>
                                <option value="">Aula da falta…</option>
                                @foreach ($aulasPorTreinamento[$ticket->treinamento?->id] ?? [] as $aula)
                                    <option value="{{ $aula->id }}">
                                        {{ $aula->treinamento->nome }} · {{ $aula->titulo }} ({{ $aula->data_inicio->format('d/m') }})
                                    </option>
                                @endforeach
                            </select>
                            <select name="status_justificativa">
                                <option value="pendente">pendente</option>
                                <option value="aprovado">aprovado</option>
                                <option value="recusado">recusado</option>
                            </select>
                            <button class="btn btn-primary btn-pequeno">Resolver (RF12)</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('portal.rh.tickets.resolver', $ticket) }}" class="form-inline">@csrf
                            <input type="hidden" name="acao" value="encerrar">
                            <input type="text" name="resultado" placeholder="Resposta ao colaborador" maxlength="1000">
                            <button class="btn btn-outline btn-pequeno">Encerrar com resposta</button>
                        </form>
                    @endif
                </div>
            @endif
        </div>
    </div>
@empty
    <p class="muted">Nenhum ticket.</p>
@endforelse
    </div>
</div>
@endsection
