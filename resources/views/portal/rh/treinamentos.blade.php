@extends('layouts.portal')

@section('titulo', 'Treinamentos — Presenza')

@section('conteudo')
<p><a href="{{ route('portal') }}">← Voltar ao portal</a></p>
<h1>Treinamentos e aulas <span class="muted">(RF07)</span></h1>
@if (session('ok'))<p class="flash-ok">{{ session('ok') }}</p>@endif
@if ($errors->any())<p class="flash-erros">{{ $errors->first() }}</p>@endif

<div class="card">
    <h2>Novo treinamento</h2>
    <form method="POST" action="{{ route('portal.rh.treinamentos.criar') }}" class="form-inline">@csrf
        <input type="text" name="nome" placeholder="Nome (ex.: NR-35 Trabalho em Altura)" maxlength="150" required>
        <input type="number" name="carga_horaria" placeholder="Carga (h)" min="1" max="500" required style="width: 7rem;">
        <input type="number" name="validade_meses" placeholder="Validade (meses)" min="1" max="120" style="width: 8rem;">
        <select name="setor_id">
            <option value="">Todos os setores</option>
            @foreach ($setores as $setor)
                <option value="{{ $setor->id }}">{{ $setor->nome }}</option>
            @endforeach
        </select>
        <button class="btn btn-primary btn-pequeno">Criar</button>
    </form>
</div>

@forelse ($treinamentos as $treinamento)
    <div class="card">
        <h2>{{ $treinamento->nome }}
            <span class="muted">
                · {{ $treinamento->carga_horaria }}h · validade {{ $treinamento->validade_meses ?? '—' }} meses
                · {{ $treinamento->setor?->nome ?? 'todos os setores' }}
            </span>
        </h2>

        @php $editavel = auth()->user()->temPapel('rh') || auth()->user()->temPapel('admin') || (auth()->user()->temPapel('lider') && $treinamento->setor_id === auth()->user()->setor_id); @endphp

        <h3>Aulas ({{ $treinamento->aulas->count() }})</h3>
        <table class="table">
            @forelse ($treinamento->aulas->sortBy('ordem') as $aula)
                <tr>
                    <td><strong>{{ $aula->ordem }}</strong></td>
                    <td>
                        <span class="badge badge-{{ $aula->status->value }}">{{ $aula->status->value }}</span>
                        {{ $aula->titulo }}
                    </td>
                    <td class="muted">{{ $aula->data_inicio->format('d/m/Y H:i') }} · {{ $aula->local }} · {{ $aula->vagasRestantes() }}/{{ $aula->vagas }}</td>
                    <td>
                        @if ($editavel && $aula->status === 'agendada')
                            <form method="POST" action="{{ route('portal.rh.treinamentos.aula.atualizar', $aula) }}" class="form-inline">@csrf @method('PATCH')
                                <input type="datetime-local" name="data_inicio" value="{{ $aula->data_inicio->format('Y-m-d\TH:i') }}" required style="width: 14rem;">
                                <input type="number" name="vagas" value="{{ $aula->vagas }}" min="1" max="300" required style="width: 5rem;">
                                <button class="btn btn-outline btn-pequeno">Ajustar</button>
                            </form>
                        @else
                            <span class="muted">{{ $aula->status === 'agendada' ? '—' : 'janela fechada: ajuste pelo instrutor (RF06)' }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td class="muted">Nenhuma aula ainda.</td></tr>
            @endforelse
        </table>

        @if ($editavel)
            <form method="POST" action="{{ route('portal.rh.treinamentos.aula.criar', $treinamento) }}" class="form-inline">@csrf
                <input type="text" name="titulo" placeholder="Título da aula" maxlength="150" required>
                <input type="datetime-local" name="data_inicio" required>
                <input type="number" name="vagas" placeholder="Vagas" min="1" max="300" required style="width: 5rem;">
                <input type="text" name="local" placeholder="Local" maxlength="120">
                <button class="btn btn-primary btn-pequeno">+ Aula {{ $treinamento->aulas->count() + 1 }}ª</button>
            </form>
        @endif
    </div>
@empty
    <p class="muted">Nenhum treinamento.</p>
@endforelse
@endsection
