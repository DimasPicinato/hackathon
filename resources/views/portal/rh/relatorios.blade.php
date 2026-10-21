@extends('layouts.portal')

@section('titulo', 'Relatórios — Presenza')

@section('conteudo')
<a class="btn-voltar" href="{{ route('portal') }}"><x-icon nome="seta-esquerda"/> Voltar</a>
<h1>Relatórios <span class="muted">(RF28)</span></h1>

<div class="card">
    <h2>Exportações</h2>
    <p><a class="btn btn-primary btn-pequeno" href="{{ route('portal.rh.conformidade') }}">📊 Matriz de conformidade</a></p>
    <p><a class="btn btn-primary btn-pequeno" href="{{ route('portal.rh.relatorios.pessoas') }}">⬇ CSV — histórico por pessoa</a></p>
    <p class="muted">Presenças, certificados vigentes e validade de todos os colaboradores.</p>
</div>

<div class="card">
    <h2>Por sessão (aula)</h2>
    <table class="table">
        <tr><th>Treinamento</th><th>Aula</th><th>Data</th><th>Status</th><th>Convocados</th><th>Presentes</th><th>Faltas</th><th></th></tr>
        @forelse ($resumo as $linha)
            @php $aula = $linha['aula']; @endphp
            <tr>
                <td>{{ $aula->treinamento->nome }}</td>
                <td>{{ $aula->titulo }}</td>
                <td class="muted">{{ $aula->data_inicio->format('d/m/Y H:i') }}</td>
                <td><span class="badge badge-{{ $aula->status->value }}">{{ $aula->status->value }}</span></td>
                <td>{{ $linha['convocados'] }}</td>
                <td>{{ $linha['presentes'] }}</td>
                <td>{{ $linha['faltas'] }}</td>
                <td><a class="btn btn-outline btn-pequeno" href="{{ route('portal.rh.relatorios.sessao', $aula) }}">⬇ CSV</a></td>
            </tr>
        @empty
            <tr><td colspan="8" class="muted">Nenhuma aula.</td></tr>
        @endforelse
    </table>
</div>
@endsection
