@extends('layouts.portal')

@section('titulo', 'Portal — Presenza')

@section('conteudo')
@php
    $user = auth()->user();
@endphp

<h1>Olá, {{ explode(' ', $user->name)[0] }}! <span class="muted">({{ $user->matricula }})</span></h1>
<p class="muted">Papéis: {{ implode(', ', $user->papeis) }}</p>

@if ($user->temPapel('instrutor'))
<div class="card">
    <h2>Minhas aulas (instrutor)</h2>
    @forelse ($aulasComoInstrutor as $aula)
        <div class="linha-aula">
            <div>
                <strong>{{ $aula->treinamento->nome }}</strong> — {{ $aula->titulo }}
                <span class="badge badge-{{ $aula->status->value }}">{{ $aula->status->value }}</span>
                <div class="muted">{{ $aula->data_inicio->format('d/m H:i') }} · {{ $aula->local }} · {{ $aula->vagasRestantes() }} vaga(s) restante(s)</div>
            </div>
            <div class="linha-acoes">
                @if ($aula->status === \App\Enums\StatusAula::Agendada)
                    <form method="POST" action="{{ route('portal.aula.iniciar', $aula) }}">@csrf
                        <button class="btn btn-primary btn-pequeno">▶ Iniciar aula</button>
                    </form>
                @elseif ($aula->status === \App\Enums\StatusAula::EmAndamento)
                    <a class="btn btn-accent btn-pequeno" href="{{ route('portal.aula.mostrar', $aula) }}">Fazer chamada</a>
                    <form method="POST" action="{{ route('portal.aula.encerrar', $aula) }}">@csrf
                        <button class="btn btn-outline btn-pequeno">■ Encerrar aula</button>
                    </form>
                @else
                    <a class="btn btn-outline btn-pequeno" href="{{ route('portal.aula.mostrar', $aula) }}">Ver chamada</a>
                @endif
            </div>
        </div>
    @empty
        <p class="muted">Nenhuma aula atribuída.</p>
    @endforelse
</div>
@endif

@if ($user->temPapel('rh') || $user->temPapel('admin'))
<div class="card">
    <h2>Gestão ({{ $user->temPapel('admin') ? 'Admin' : 'RH' }}) — fila de trabalho</h2>
    <div class="grid-2">
        <div>
            <h3>Tickets abertos ({{ $pendentes['tickets']->count() }})</h3>
            <table class="table">
                @forelse ($pendentes['tickets'] as $ticket)
                    <tr>
                        <td>{{ $ticket->user->name }}</td>
                        <td>{{ $ticket->tipoTicket->nome }}</td>
                        <td class="muted">{{ $ticket->treinamento?->nome }}</td>
                    </tr>
                @empty
                    <tr><td class="muted">Nenhum.</td></tr>
                @endforelse
            </table>
        </div>
        <div>
            <h3>Justificativas pendentes ({{ $pendentes['justificativas']->count() }})</h3>
            <table class="table">
                @forelse ($pendentes['justificativas'] as $just)
                    <tr>
                        <td>{{ $just->user->name }}</td>
                        <td class="muted">{{ $just->aula->treinamento->nome }}</td>
                        <td><span class="badge badge-pendente">pendente</span></td>
                    </tr>
                @empty
                    <tr><td class="muted">Nenhuma.</td></tr>
                @endforelse
            </table>
        </div>
    </div>
    <nav class="form-inline">
        <a class="btn btn-primary btn-pequeno" href="{{ route('portal.rh.tickets') }}">Tickets ({{ $pendentes['tickets']->count() }})</a>
        <a class="btn btn-primary btn-pequeno" href="{{ route('portal.rh.justificativas') }}">Justificativas ({{ $pendentes['justificativas']->count() }})</a>
        <a class="btn btn-primary btn-pequeno" href="{{ route('portal.rh.certificados') }}">Certificados</a>
        <a class="btn btn-primary btn-pequeno" href="{{ route('portal.rh.relatorios') }}">Relatórios</a>
    </nav>
</div>
@endif

@if ($user->temPapel('lider'))
<div class="card">
    <h2>Meu setor: {{ $user->setor?->nome }}</h2>
    <p>{{ $teamCount }} liderados.</p>
    <nav class="form-inline">
        <a class="btn btn-primary btn-pequeno" href="{{ route('portal.rh.tickets') }}">Tickets do setor (RF12)</a>
        <a class="btn btn-primary btn-pequeno" href="{{ route('portal.rh.justificativas') }}">Justificativas (RF13)</a>
        <a class="btn btn-primary btn-pequeno" href="{{ route('portal.rh.relatorios') }}">Relatórios (RF28)</a>
    </nav>
</div>
@endif

<div class="card">
    <h2>Minhas aulas e presenças</h2>
    @forelse ($inscricoes as $inscricao)
        @php $aula = $inscricao->aula; @endphp
        <div class="linha-aula">
            <div>
                <strong>{{ $aula->treinamento->nome }}</strong> — {{ $aula->titulo }}
                @if ($inscricao->status === 'lista_espera')
                    <span class="badge badge-fila">fila de espera · posição {{ $inscricao->posicao_fila }}</span>
                @elseif ($inscricao->status === 'reservada')
                    <span class="badge badge-manual">vaga nominal</span>
                @else
                    <span class="badge badge-recusado">cancelada</span>
                @endif
                <div class="muted">{{ $aula->data_inicio->format('d/m H:i') }} · {{ $aula->local }}
                    @if ($aula->presencas->firstWhere('user_id', $user->id))
                        · presença: <span class="badge badge-{{ $aula->presencas->firstWhere('user_id', $user->id)->metodo->value }}">{{ $aula->presencas->firstWhere('user_id', $user->id)->metodo->value }}</span>
                    @endif
                </div>
            </div>
            <div class="linha-acoes">
                @if ($aula->estaAtiva() && $inscricao->status === 'reservada')
                    <form method="POST" action="{{ route('portal.presenca.facial') }}">@csrf
                        <input type="hidden" name="aula_id" value="{{ $aula->id }}">
                        <button class="btn btn-accent">📷 Validar presença agora (facial)</button>
                    </form>
                @endif
            </div>
        </div>
    @empty
        <p class="muted">Nenhuma inscrição.</p>
    @endforelse
</div>

<div class="grid-2">
    <div class="card">
        <h2>Meus certificados</h2>
        <table class="table">
            @forelse ($certificados as $cert)
                <tr>
                    <td>{{ $cert->treinamento->nome }}</td>
                    <td><code>{{ $cert->codigo }}</code></td>
                    <td><a href="{{ route('verificar.mostrar', $cert->codigo) }}" target="_blank">verificar</a></td>
                </tr>
            @empty
                <tr><td class="muted">Nenhum ainda.</td></tr>
            @endforelse
        </table>
    </div>
    <div class="card">
        <h2>Minhas justificativas e tickets</h2>
        <table class="table">
            @foreach ($justificativas as $just)
                <tr>
                    <td>Jus. {{ $just->aula->treinamento->nome }}</td>
                    <td><span class="badge badge-{{ $just->status->value }}">{{ $just->status->value }}</span></td>
                </tr>
            @endforeach
            @foreach ($tickets as $ticket)
                <tr>
                    <td>Ticket · {{ $ticket->tipoTicket->nome }}</td>
                    <td><span class="badge badge-pendente">{{ $ticket->status->value }}</span></td>
                </tr>
            @endforeach
            @if ($tickets->isEmpty() && $justificativas->isEmpty())
                <tr><td class="muted">Nenhum.</td></tr>
            @endif
        </table>
    </div>
</div>

<div class="card">
    <h2>Notificações</h2>
    @forelse ($notificacoes as $not)
        <div class="linha-aula">
            <div>
                <strong>{{ $not->titulo }}</strong>
                <div class="muted">{{ $not->corpo }} · {{ $not->created_at->format('d/m H:i') }}</div>
            </div>
        </div>
    @empty
        <p class="muted">Nada por aqui.</p>
    @endforelse
</div>
@endsection
