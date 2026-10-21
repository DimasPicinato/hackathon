<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Concerns\EscopoGestao;
use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use App\Models\Justificativa;
use App\Models\Notificacao;
use App\Models\User;
use Illuminate\Http\Request;

// RF12/RF13 — status de justificativa a qualquer momento; Líder registra direto pelo liderado
class JustificativasController extends Controller
{
    use EscopoGestao;

    public function index(Request $request)
    {
        $this->garantirGestor();

        $justificativas = Justificativa::with(['user.setor', 'aula.treinamento', 'alteradoPor'])
            ->orderByDesc('alterado_em')->orderByDesc('created_at')->get();

        if ($request->user()->temPapel('lider')) {
            $justificativas = $justificativas->filter(
                fn ($j) => $j->user->setor_id === $request->user()->setor_id
            )->values();
        }

        return view('portal.rh.justificativas', ['justificativas' => $justificativas]);
    }

    public function alterarStatus(Request $request, Justificativa $justificativa)
    {
        $this->garantirGestor();
        $this->autorizarSobre($justificativa->user);

        $dados = $request->validate(['status' => 'required|in:pendente,aprovado,recusado']);

        $justificativa->update([
            'status' => $dados['status'],
            'alterado_por_id' => $request->user()->id,
            'alterado_em' => now(),
        ]);

        Auditoria::registrar('justificativa_alterada', $justificativa, [
            'novo_status' => $dados['status'],
        ], $request->user()->id, $request->ip());

        Notificacao::create([ // RF24: mudança de justificativa notifica o colaborador
            'user_id' => $justificativa->user_id,
            'titulo' => 'Justificativa da falta: ' . $dados['status'],
            'corpo' => "{$justificativa->aula->treinamento->nome} — {$justificativa->aula->titulo}",
            'tipo' => 'justificativa_alterada',
        ]);

        return back()->with('ok', "Justificativa de {$justificativa->user->name} agora está {$dados['status']} (RF12 — a qualquer momento).");
    }

    /** RF13 — justificativa direta por RH/Líder, sem ticket. */
    public function registrar(Request $request)
    {
        $this->garantirGestor();

        $dados = $request->validate([
            'user_id' => 'required|exists:users,id',
            'aula_id' => 'required|exists:aulas,id',
            'status' => 'required|in:pendente,aprovado,recusado',
            'texto' => 'nullable|string|max:1000',
        ]);

        $alvo = User::findOrFail($dados['user_id']);
        $this->autorizarSobre($alvo);

        $justificativa = Justificativa::updateOrCreate(
            ['user_id' => $alvo->id, 'aula_id' => $dados['aula_id']],
            [
                'ticket_id' => null,
                'status' => $dados['status'],
                'texto' => $dados['texto'] ?? null,
                'alterado_por_id' => $request->user()->id,
                'alterado_em' => now(),
            ],
        );

        Auditoria::registrar('justificativa_registrada', $justificativa, [
            'via' => 'rf13',
            'status' => $dados['status'],
        ], $request->user()->id, $request->ip());

        Notificacao::create([
            'user_id' => $alvo->id,
            'titulo' => 'Justificativa registrada pela gestão',
            'corpo' => "{$justificativa->aula->treinamento->nome} — {$justificativa->aula->titulo}: {$dados['status']}.",
            'tipo' => 'justificativa_alterada',
        ]);

        return back()->with('ok', "Justificativa registrada para {$alvo->name} (RF13) — status {$dados['status']}.");
    }
}
