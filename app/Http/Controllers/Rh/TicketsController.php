<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Concerns\EscopoGestao;
use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use App\Models\Aula;
use App\Models\Justificativa;
use App\Models\Notificacao;
use App\Models\Ticket;
use App\Services\VagaService;
use Illuminate\Http\Request;

// RF10–RF12 — tickets resolvidos por RH/Líder (escopo por setor) com efeito real
class TicketsController extends Controller
{
    use EscopoGestao;

    public function index(Request $request)
    {
        $this->garantirGestor();

        $tickets = Ticket::with(['user.setor', 'treinamento', 'tipoTicket'])
            ->orderBy('status')->orderByDesc('created_at')->get();

        if ($request->user()->temPapel('lider')) {
            $tickets = $tickets->filter(
                fn ($t) => $t->user->setor_id === $request->user()->setor_id
            )->values();
        }

        $aulasPorTreinamento = Aula::with('treinamento')->orderBy('ordem')->get()->groupBy('treinamento_id');

        return view('portal.rh.tickets', [
            'tickets' => $tickets,
            'aulasPorTreinamento' => $aulasPorTreinamento,
        ]);
    }

    public function resolver(Request $request, Ticket $ticket)
    {
        $this->garantirGestor();
        $this->autorizarSobre($ticket->user);

        $dados = $request->validate([
            'acao' => 'required|in:cancelar_vaga,justificar_falta,encerrar',
            'aula_id' => 'nullable|exists:aulas,id',
            'status_justificativa' => 'nullable|in:aprovado,recusado',
            'resultado' => 'nullable|string|max:1000',
        ]);

        $resultado = match ($dados['acao']) {
            'cancelar_vaga' => $this->resolverCancelamento($ticket, $request),
            'justificar_falta' => $this->resolverJustificativa($ticket, $dados, $request),
            default => $dados['resultado'] ?: 'Ticket encerrado sem ação.',
        };

        $ticket->update([
            'status' => 'resolvido',
            'resolvido_por_id' => $request->user()->id,
            'resolvido_em' => now(),
            'resultado' => $resultado,
        ]);

        Auditoria::registrar('ticket_resolvido', $ticket, [
            'acao' => $dados['acao'],
            'mensagem' => $resultado,
        ], $request->user()->id, $request->ip());

        Notificacao::create([ // RF24: resultado do ticket chega ao colaborador
            'user_id' => $ticket->user_id,
            'titulo' => 'Seu ticket foi resolvido',
            'corpo' => $resultado,
            'tipo' => 'ticket_resolvido',
        ]);

        return back()->with('ok', "Ticket #{$ticket->id} resolvido — {$resultado}");
    }

    /** RF09: resolver "Cancelar vaga" devolve as vagas em TODAS as aulas + promove a fila. */
    private function resolverCancelamento(Ticket $ticket, Request $request): string
    {
        $treinamento = $ticket->treinamento;
        abort_if($treinamento === null, 422, 'Ticket sem treinamento associado.');

        $liberadas = app(VagaService::class)->devolverVagas(
            $ticket->user, $treinamento, $request->user()->id, $request->ip(),
        );

        return "Vagas canceladas em {$liberadas} aula(s); fila de espera promovida automaticamente (RF09).";
    }

    /** RF12: resolver "Justificar falta" cria/altera a justificativa da aula escolhida. */
    private function resolverJustificativa(Ticket $ticket, array $dados, Request $request): string
    {
        $aula = Aula::find($dados['aula_id'] ?? null);
        abort_if($aula === null, 422, 'Escolha a aula da falta.');

        $status = $dados['status_justificativa'] ?? 'pendente';

        $justificativa = Justificativa::updateOrCreate(
            ['user_id' => $ticket->user_id, 'aula_id' => $aula->id],
            [
                'ticket_id' => $ticket->id,
                'status' => $status,
                'texto' => $ticket->texto,
                'alterado_por_id' => $request->user()->id,
                'alterado_em' => now(),
            ],
        );

        Auditoria::registrar('justificativa_alterada', $justificativa, [
            'via' => 'ticket #' . $ticket->id,
            'status' => $status,
        ], $request->user()->id, $request->ip());

        Notificacao::create([
            'user_id' => $ticket->user_id,
            'titulo' => 'Justificativa da falta: ' . $status,
            'corpo' => "{$aula->treinamento->nome} — {$aula->titulo}",
            'tipo' => 'justificativa_alterada',
        ]);

        return "Falta em {$aula->treinamento->nome} — {$aula->titulo} justificada como {$status}.";
    }
}
