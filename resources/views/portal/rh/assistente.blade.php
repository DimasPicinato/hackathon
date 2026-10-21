@extends('layouts.portal')

@section('titulo', 'Conversas do assistente — Presenza')

@section('conteudo')
<a class="btn-voltar" href="{{ route('portal') }}"><x-icon nome="seta-esquerda"/> Voltar</a>
<h1>Conversas do assistente</h1>
<p class="muted">Registro de auditoria do Assistente Presenza (somente Admin). O histórico do visitante vive apenas na aba dele.</p>

<div class="card">
    <table class="table" id="tabela-conversas">
        <thead>
            <tr>
                <th>Quando</th>
                <th>Sessão</th>
                <th>Estado</th>
                <th>Pergunta</th>
                <th>Resposta</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($mensagens as $mensagem)
                <tr>
                    <td>{{ $mensagem->created_at?->format('d/m/Y H:i') }}</td>
                    <td><code>{{ \Illuminate\Support\Str::limit($mensagem->sessao, 12) }}</code></td>
                    <td>
                        <span class="badge badge-{{ $mensagem->estado === 'ok' ? 'aprovado' : 'manual' }}">
                            {{ $mensagem->estado === 'ok' ? 'respondida' : 'cortesia' }}
                        </span>
                    </td>
                    <td>{{ $mensagem->pergunta }}</td>
                    <td>{{ $mensagem->resposta }}</td>
                    <td>{{ $mensagem->ip ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6">Nenhuma conversa registrada ainda.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="acoes-login">
        @if ($mensagens->onFirstPage())
            <span></span>
        @else
            <a class="btn btn-outline btn-pequeno" href="{{ $mensagens->previousPageUrl() }}">Anteriores</a>
        @endif

        <span class="muted">Página {{ $mensagens->currentPage() }} de {{ $mensagens->lastPage() }}</span>

        @if ($mensagens->hasMorePages())
            <a class="btn btn-outline btn-pequeno" href="{{ $mensagens->nextPageUrl() }}">Próximas</a>
        @else
            <span></span>
        @endif
    </div>
</div>
@endsection
