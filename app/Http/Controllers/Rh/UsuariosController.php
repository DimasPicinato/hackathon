<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Concerns\EscopoGestao;
use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use App\Models\Notificacao;
use App\Models\Setor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

// RF05 — CRUD de usuários sem self-service: RH/Admin; Líder só redefinir senha dos liderados
class UsuariosController extends Controller
{
    use EscopoGestao;

    private const PAPEIS = ['colaborador', 'lider', 'instrutor', 'rh', 'admin'];
    private const TURNOS = ['manha', 'tarde', 'noite'];

    public function index(Request $request)
    {
        $this->garantirGestor();

        $usuarios = User::query()->with('setor')->orderBy('name')->get();

        if ($request->user()->temPapel('lider')) {
            $usuarios = $usuarios->where('setor_id', $request->user()->setor_id)->values();
        }

        return view('portal.rh.usuarios', [
            'usuarios' => $usuarios,
            'setores' => Setor::orderBy('nome')->get(),
            'papeis' => self::PAPEIS,
            'turnos' => self::TURNOS,
            'souRh' => $request->user()->temPapel('rh') || $request->user()->temPapel('admin'),
            'senhapadrao' => 'Password@123',
        ]);
    }

    public function criar(Request $request)
    {
        $this->garantirRh();

        $dados = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:150|unique:users,email',
            'matricula' => 'required|string|max:30|unique:users,matricula',
            'setor_id' => 'nullable|exists:setores,id',
            'turno' => 'nullable|in:' . implode(',', self::TURNOS),
            'papeis' => 'required|array|min:1',
            'papeis.*' => 'in:' . implode(',', self::PAPEIS),
            'password' => ['required', 'string', Password::min(8)],
        ]);

        $usuario = User::create($dados + ['ativo' => true]);

        Auditoria::registrar('usuario_criado', $usuario, [
            'papeis' => $usuario->papeis,
            'setor' => $usuario->setor?->nome,
        ], $request->user()->id, $request->ip());

        Notificacao::create([ // RF24: boas-vindas
            'user_id' => $usuario->id,
            'titulo' => 'Bem-vindo(a) à Presenza!',
            'corpo' => 'Sua conta foi criada pelo RH. Ao entrar, aceite o termo LGPD.',
            'tipo' => 'conta',
        ]);

        return back()->with('ok', "{$usuario->name} criado(a) — senha inicial configurada pelo RH (RF05).");
    }

    public function atualizar(Request $request, User $usuario)
    {
        $this->garantirRh();

        $dados = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:150|unique:users,email,' . $usuario->id,
            'setor_id' => 'nullable|exists:setores,id',
            'turno' => 'nullable|in:' . implode(',', self::TURNOS),
            'papeis' => 'required|array|min:1',
            'papeis.*' => 'in:' . implode(',', self::PAPEIS),
        ]);

        $usuario->update($dados);

        Auditoria::registrar('usuario_ajustado', $usuario, [
            'papeis' => $usuario->papeis,
        ], $request->user()->id, $request->ip());

        return back()->with('ok', "{$usuario->name} atualizado(a) (RF05).");
    }

    public function alternarAtivo(Request $request, User $usuario)
    {
        $this->garantirRh();

        $usuario->update(['ativo' => ! $usuario->ativo]);

        Auditoria::registrar('usuario_' . ($usuario->ativo ? 'reativado' : 'desativado'), $usuario, [], $request->user()->id, $request->ip());

        return back()->with('ok', "{$usuario->name} agora está " . ($usuario->ativo ? 'ativo' : 'DESATIVADO (perde acesso ao portal)') . '.');
    }

    public function redefinirSenha(Request $request, User $usuario)
    {
        $this->garantirGestor();
        $this->autorizarSobre($usuario); // RF05: Líder só sobre liderados; RH em todos

        $dados = $request->validate([
            'senha' => ['nullable', 'string', Password::min(8)],
        ]);

        $nova = $dados['senha'] ?? 'Password@123';
        $usuario->update(['password' => Hash::make($nova)]);

        Auditoria::registrar('senha_redefinida', $usuario, [], $request->user()->id, $request->ip());

        Notificacao::create([
            'user_id' => $usuario->id,
            'titulo' => 'Sua senha foi redefinida pela gestão',
            'corpo' => 'Use a nova senha temporária no próximo login.',
            'tipo' => 'conta',
        ]);

        return back()->with('ok', "Senha de {$usuario->name} redefinida (RF05).");
    }
}
