@extends('layouts.portal')

@section('titulo', 'Matriz de conformidade — Presenza')

@section('conteudo')
<a class="btn-voltar" href="{{ route('portal') }}"><x-icon nome="seta-esquerda"/> Voltar</a>
<h1>Matriz de conformidade <span class="muted">(quem precisa fazer o quê?)</span></h1>


<div class="card" style="overflow-x: auto;">
    <div class="cabeca-tabela">
        <h2>Pessoas × treinamentos</h2>
        <input type="search" class="campo-busca" data-busca="tabela-matriz" placeholder="Buscar por nome ou setor…" aria-label="Buscar na matriz">
    </div>
    <table class="table" id="tabela-matriz">
        <thead>
        <tr>
            <th>Pessoa</th>
            <th>Setor</th>
            @foreach ($treinamentos as $treinamento)
                <th>{{ $treinamento->nome }}<br><span class="muted">validade {{ $treinamento->validade_meses ?? '—' }}m</span></th>
            @endforeach
        </tr>
        </thead>
        <tbody>
        @foreach ($pessoas as $pessoa)
            <tr>
                <td><strong>{{ $pessoa->name }}</strong></td>
                <td class="muted">{{ $pessoa->setor?->nome ?? '—' }}</td>
                @foreach ($treinamentos as $treinamento)
                    @php
                        $celula = $matriz[$pessoa->id][$treinamento->id] ?? null;
                        $legenda = ['vigente' => 'aprovado', 'expirado' => 'recusado', 'em_curso' => 'pendente', 'pendente' => 'fila', 'sem_oferta' => 'agendada'];
                    @endphp
                    @if ($celula)
                        <td>
                            <span class="badge badge-{{ $legenda[$celula[0]] }}">{{ $celula[0] }}</span>
                            <span class="muted">{{ $celula[1] }}</span>
                        </td>
                    @else
                        <td><span class="muted">n/a</span></td>
                    @endif
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
