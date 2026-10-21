@extends('layouts.portal')

@section('titulo', 'Usuários — Presenza')

@section('conteudo')
<a class="btn-voltar" href="{{ route('portal') }}"><x-icon nome="seta-esquerda"/> Voltar</a>
<h1>Usuários</h1>

@if ($errors->any())<p class="flash-erros">{{ $errors->first() }}</p>@endif

@if ($souRh)
<div class="card">
    <h2>Novo usuário</h2>
    <form method="POST" action="{{ route('portal.rh.usuarios.criar') }}" class="grid-formulario">@csrf
        <div>
            <label for="novo-name">Nome completo</label>
            <input id="novo-name" type="text" name="name" maxlength="120" required>
        </div>
        <div>
            <label for="novo-email">E-mail corporativo</label>
            <input id="novo-email" type="email" name="email" maxlength="150" required>
        </div>
        <div>
            <label for="novo-matricula">Matrícula</label>
            <input id="novo-matricula" type="text" name="matricula" maxlength="30" required>
        </div>
        <div>
            <label for="novo-setor">Setor</label>
            <select id="novo-setor" name="setor_id">
                <option value="">Selecione…</option>
                @foreach ($setores as $setor)
                    <option value="{{ $setor->id }}">{{ $setor->nome }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="novo-turno">Turno</label>
            <select id="novo-turno" name="turno">
                <option value="">Selecione…</option>
                @foreach ($turnos as $turno)
                    <option value="{{ $turno }}">{{ $turno }}</option>
                @endforeach
            </select>
        </div>
        <div class="campo-largo">
            <label for="novo-papeis">Papéis (segure Ctrl p/ vários)</label>
            <select id="novo-papeis" name="papeis[]" multiple size="3" required>
                @foreach ($papeis as $papel)
                    <option value="{{ $papel }}">{{ $papel }}</option>
                @endforeach
            </select>
        </div>
        <div class="campo-largo">
            <label for="novo-senha">Senha inicial</label>
            <input id="novo-senha" type="password" name="password" placeholder="mín. 8 caracteres" required autocomplete="new-password">
        </div>
        <div class="form-envio">
            <button class="btn btn-primary">Criar conta</button>
        </div>
    </form>
    <p class="muted">Papéis são acumuláveis (ex.: colaborador + instrutor). Sem self-service: ninguém se cadastra sozinho.</p>
</div>
@endif

<div class="card">
    <div class="cabeca-tabela">
        <h2>Pessoas no escopo</h2>
        <input type="search" class="campo-busca" data-busca="tabela-usuarios" placeholder="Buscar por nome, matrícula, setor, papel…" aria-label="Buscar usuários">
    </div>
    <table class="table" id="tabela-usuarios">
        <thead>
            <tr>
                <th>Usuário</th>
                <th>Setor</th>
                <th>Turno</th>
                <th>Papéis</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($usuarios as $usuario)
            <tr>
                <td>
                    <strong>{{ $usuario->name }}</strong><br>
                    <span class="muted">{{ $usuario->email }} · {{ $usuario->matricula }}</span>
                </td>
                <td>{{ $usuario->setor?->nome ?? '—' }}</td>
                <td>{{ $usuario->turno ?? '—' }}</td>
                <td>
                    @foreach ($usuario->papeis as $papel)
                        <span class="badge badge-fila">{{ $papel }}</span>
                    @endforeach
                </td>
                <td>
                    <span class="badge badge-{{ $usuario->ativo ? 'aprovado' : 'recusado' }}">{{ $usuario->ativo ? 'ativo' : 'desativado' }}</span>
                </td>
                <td class="celula-acoes">
                    @if ($souRh)
                        <form method="POST" action="{{ route('portal.rh.usuarios.alternar', $usuario) }}">@csrf
                            <button class="btn btn-outline btn-pequeno"
                                    onclick="return confirm('{{ $usuario->ativo ? 'Desativar' : 'Reativar' }} esta conta?')">
                                {{ $usuario->ativo ? 'Desativar' : 'Reativar' }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('portal.rh.usuarios.senha', $usuario) }}" class="form-edicao">@csrf
                            <input type="password" name="senha" placeholder="Nova senha (vazio = padrão)" autocomplete="new-password">
                            <button class="btn btn-outline btn-pequeno">Redefinir senha</button>
                        </form>
                        <form method="POST" action="{{ route('portal.rh.usuarios.atualizar', $usuario) }}" class="form-edicao">@csrf @method('PATCH')
                            <input type="text" name="name" value="{{ $usuario->name }}" maxlength="120" required>
                            <input type="email" name="email" value="{{ $usuario->email }}" maxlength="150" required>
                            <select name="setor_id">
                                <option value="">Setor…</option>
                                @foreach ($setores as $setor)
                                    <option value="{{ $setor->id }}" @selected($usuario->setor_id === $setor->id)>{{ $setor->nome }}</option>
                                @endforeach
                            </select>
                            <select name="turno">
                                <option value="">Turno…</option>
                                @foreach ($turnos as $turno)
                                    <option value="{{ $turno }}" @selected($usuario->turno === $turno)>{{ $turno }}</option>
                                @endforeach
                            </select>
                            <select name="papeis[]" multiple size="2">
                                @foreach ($papeis as $papel)
                                    <option value="{{ $papel }}" @selected(in_array($papel, $usuario->papeis))>{{ $papel }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-primary btn-pequeno">Salvar</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('portal.rh.usuarios.senha', $usuario) }}" class="form-edicao">@csrf
                            <input type="password" name="senha" placeholder="Senha temporária" autocomplete="new-password" required>
                            <button class="btn btn-outline btn-pequeno">Redefinir senha</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6"><p class="muted">Nenhuma pessoa no escopo.</p></td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
