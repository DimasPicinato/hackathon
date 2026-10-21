@extends('layouts.portal')

@section('titulo', 'UsuÃ¡rios â€” Presenza')

@section('conteudo')
<p><a href="{{ route('portal') }}">â† Voltar ao portal</a></p>
<h1>UsuÃ¡rios <span class="muted">(RF05 â€” sem self-service)</span></h1>
@if (session('ok'))<p class="flash-ok">{{ session('ok') }}</p>@endif
@if ($errors->any())<p class="flash-erros">{{ $errors->first() }}</p>@endif

@if ($souRh)
<div class="card">
    <h2>Novo usuÃ¡rio</h2>
    <form method="POST" action="{{ route('portal.rh.usuarios.criar') }}" class="form-inline">@csrf
        <input type="text" name="name" placeholder="Nome completo" maxlength="120" required>
        <input type="email" name="email" placeholder="email" maxlength="150" required>
        <input type="text" name="matricula" placeholder="MatrÃ­cula" maxlength="30" required style="width: 8rem;">
        <select name="setor_id">
            <option value="">Setorâ€¦</option>
            @foreach ($setores as $setor)
                <option value="{{ $setor->id }}">{{ $setor->nome }}</option>
            @endforeach
        </select>
        <select name="turno">
            <option value="">Turnoâ€¦</option>
            @foreach ($turnos as $turno)
                <option value="{{ $turno }}">{{ $turno }}</option>
            @endforeach
        </select>
        <select name="papeis[]" multiple size="3" required>
            @foreach ($papeis as $papel)
                <option value="{{ $papel }}">{{ $papel }}</option>
            @endforeach
        </select>
        <input type="password" name="password" placeholder="Senha inicial (mÃ­n. 8)" required autocomplete="new-password">
        <button class="btn btn-primary btn-pequeno">Criar</button>
    </form>
    <p class="muted">PapÃ©is sÃ£o acumulÃ¡veis (ex.: colaborador + instrutor). Sem self-service: ninguÃ©m se cadastra sozinho.</p>
</div>
@endif

@forelse ($usuarios as $usuario)
    <div class="card">
        <div class="linha-aula">
            <div>
                <strong>{{ $usuario->name }}</strong>
                <span class="muted">({{ $usuario->matricula }} Â· {{ $usuario->setor?->nome ?? 'sem setor' }} Â· {{ $usuario->turno ?? 'sem turno' }})</span><br>
                <span class="muted">{{ $usuario->email }}</span>
                @foreach ($usuario->papeis as $papel)
                    <span class="badge badge-fila">{{ $papel }}</span>
                @endforeach
            </div>
            <div class="linha-acoes">
                <span class="badge badge-{{ $usuario->ativo ? 'aprovado' : 'recusado' }}">{{ $usuario->ativo ? 'ativo' : 'desativado' }}</span>
                @if ($souRh)
                    <form method="POST" action="{{ route('portal.rh.usuarios.alternar', $usuario) }}">@csrf
                        <button class="btn btn-outline btn-pequeno"
                                onclick="return confirm('{{ $usuario->ativo ? 'Desativar' : 'Reativar' }} esta conta?')">
                            {{ $usuario->ativo ? 'Desativar' : 'Reativar' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('portal.rh.usuarios.senha', $usuario) }}" class="form-inline">@csrf
                        <input type="password" name="senha" placeholder="Nova senha (vazio = padrÃ£o)" autocomplete="new-password">
                        <button class="btn btn-outline btn-pequeno">Redefinir senha</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('portal.rh.usuarios.senha', $usuario) }}" class="form-inline">@csrf
                        <input type="password" name="senha" placeholder="Senha temporÃ¡ria" autocomplete="new-password" required>
                        <button class="btn btn-outline btn-pequeno">Redefinir senha (RF05)</button>
                    </form>
                @endif
            </div>
        </div>

        @if ($souRh)
            <form method="POST" action="{{ route('portal.rh.usuarios.atualizar', $usuario) }}" class="form-inline" style="margin-top: .5rem;">@csrf @method('PATCH')
                <input type="text" name="name" value="{{ $usuario->name }}" maxlength="120" required>
                <input type="email" name="email" value="{{ $usuario->email }}" maxlength="150" required>
                <select name="setor_id">
                    @foreach ($setores as $setor)
                        <option value="{{ $setor->id }}" @selected($usuario->setor_id === $setor->id)>{{ $setor->nome }}</option>
                    @endforeach
                </select>
                <select name="turno">
                    @foreach ($turnos as $turno)
                        <option value="{{ $turno }}" @selected($usuario->turno === $turno)>{{ $turno }}</option>
                    @endforeach
                </select>
                <select name="papeis[]" multiple size="3">
                    @foreach ($papeis as $papel)
                        <option value="{{ $papel }}" @selected(in_array($papel, $usuario->papeis))>{{ $papel }}</option>
                    @endforeach
                </select>
                <button class="btn btn-primary btn-pequeno">Salvar</button>
            </form>
        @endif
    </div>
@empty
    <p class="muted">Nenhuma pessoa no escopo.</p>
@endforelse
@endsection
