@extends('layouts.portal')

@section('titulo', 'Certificados — Presenza')

@section('conteudo')
<a class="btn-voltar" href="{{ route('portal') }}"><x-icon nome="seta-esquerda"/> Voltar</a>
<h1>Certificados <span class="muted">(RF18–RF20)</span></h1>

@if ($errors->any())<p class="flash-erros">{{ $errors->first() }}</p>@endif

@forelse ($treinamentos as $treinamento)
    <div class="card">
        <h2>{{ $treinamento->nome }}
            <span class="muted">· {{ $treinamento->aulas->count() }} aula(s) · validade {{ $treinamento->validade_meses }} meses</span>
        </h2>

        <h3>Elegíveis a emitir <span class="muted">(RF18 — presença em TODAS as aulas)</span></h3>
        @if ($elegiveisPorTreinamento[$treinamento->id]->isEmpty())
            <p class="muted">Ninguém elegível ainda.</p>
        @else
            <table class="table">
                @foreach ($elegiveisPorTreinamento[$treinamento->id] as $elegivel)
                    <tr>
                        <td>{{ $elegivel->name }} <span class="muted">({{ $elegivel->matricula }})</span></td>
                        <td>
                            @if ($podeEmitir)
                                <form method="POST" action="{{ route('portal.rh.certificados.emitir') }}">@csrf
                                    <input type="hidden" name="user_id" value="{{ $elegivel->id }}">
                                    <input type="hidden" name="treinamento_id" value="{{ $treinamento->id }}">
                                    <button class="btn btn-primary btn-pequeno">Emitir certificado (RF19)</button>
                                </form>
                            @else
                                <span class="muted">emissão exclusiva do RH (RF19)</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </table>
        @endif

        <h3>Emitidos <span class="muted">(RF20 — imutável; reemissão cria nova versão)</span></h3>
        @forelse ($treinamento->certificados as $cert)
            <div class="linha-aula">
                <div>
                    <strong>{{ $cert->user?->name }}</strong>
                    <code>{{ $cert->codigo }}</code>
                    <span class="badge badge-{{ $cert->resultadoVerificacao()->value }}">{{ $cert->resultadoVerificacao()->value }}</span>
                    <span class="muted">v{{ $cert->versao }} · emitido {{ $cert->emitido_em->format('d/m/Y') }} · válido até {{ $cert->valido_ate?->format('d/m/Y') }}</span>
                    @if ($cert->substituido_por_id)
                        <span class="badge badge-recusado">substituído</span>
                    @endif
                </div>
                <div class="linha-acoes">
                    <a class="btn btn-outline btn-pequeno" href="{{ route('verificar.mostrar', $cert->codigo) }}" target="_blank">verificar</a>
                    @if ($podeEmitir && ! $cert->substituido_por_id)
                        <form method="POST" action="{{ route('portal.rh.certificados.reemitir', $cert) }}">@csrf
                            <button class="btn btn-accent btn-pequeno"
                                    onclick="return confirm('Reemissão gera NOVA versão (nova linha); o código atual deixa de ser verificável. Continuar?')">
                                Reemitir (RF20)
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <p class="muted">Nenhum certificado deste treinamento ainda.</p>
        @endforelse
    </div>
@empty
    <p class="muted">Nenhum treinamento.</p>
@endforelse
@endsection
