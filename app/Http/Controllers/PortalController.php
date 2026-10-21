<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Aula;
use App\Models\Auditoria;
use App\Models\Certificado;
use App\Models\Inscricao;
use App\Models\Justificativa;
use App\Models\Notificacao;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;

class PortalController extends Controller
{
    // Portal único por papel (RNF05: uma tela por tarefa) — seções montadas por papel
    public function home(Request $request)
    {
        $user = $request->user();

        $inscricoes = Inscricao::query()->with(['aula.treinamento'])
            ->where('user_id', $user->id)->latest()->get();

        $dados = [
            'inscricoes' => $inscricoes,
            'certificados' => $user->certificados()->with('treinamento')->latest('id')->get(),
            'justificativas' => Justificativa::query()->with(['aula.treinamento'])
                ->where('user_id', $user->id)->latest('id')->get(),
            'tickets' => $user->tickets()->with(['tipoTicket', 'treinamento'])->latest('id')->get(),
            'notificacoes' => $user->notificacoes()->latest('id')->limit(6)->get(),
            'aulasComoInstrutor' => $user->temPapel('instrutor')
                ? $user->aulasComoInstrutor()->with('treinamento')->orderBy('data_inicio')->get()
                : collect(),
            'pendentes' => $user->temPapel('rh') || $user->temPapel('admin')
                ? [
                    'tickets' => Ticket::query()->with(['user', 'tipoTicket', 'treinamento'])
                        ->where('status', 'aberto')->latest('id')->get(),
                    'justificativas' => Justificativa::query()->with(['user', 'aula.treinamento', 'ticket'])
                        ->where('status', 'pendente')->latest('id')->get(),
                ]
                : null,
            'teamCount' => $user->temPapel('lider') && $user->setor_id
                ? $user->setor->users()->count()
                : 0,
        ];

        return view('portal.home', $dados);
    }

    // RF10/RF11 — colaborador só SUBMETE o ticket (catálogo + texto livre); resultado nunca é editado por ele
    public function abrirTicket(Request $request)
    {
        $dados = $request->validate([
            'tipo_ticket_id' => 'required|exists:tipos_ticket,id,ativo,1',
            'treinamento_id' => 'nullable|exists:treinamentos,id',
            'texto' => 'required|string|max:1000',
        ]);

        $ticket = Ticket::create([
            'user_id' => $request->user()->id,
            'treinamento_id' => $dados['treinamento_id'] ?? null,
            'tipo_ticket_id' => $dados['tipo_ticket_id'],
            'texto' => $dados['texto'],
            'status' => 'aberto',
        ]);

        Auditoria::registrar('ticket_aberto', $ticket, [
            'tipo' => $ticket->tipoTicket->nome,
        ], $request->user()->id, $request->ip());

        User::query()->where('ativo', true)->whereJsonContains('papeis', 'rh') // RF24: ticket novo → RH
            ->get()->each(function ($rh) use ($ticket) {
                Notificacao::create([
                    'user_id' => $rh->id,
                    'titulo' => 'Novo ticket: ' . $ticket->tipoTicket->nome,
                    'corpo' => $ticket->user->name
                        . ($ticket->treinamento ? ' — ' . $ticket->treinamento->nome : ''),
                    'tipo' => 'ticket_novo',
                ]);
            });

        return back()->with('ok', "Ticket #{$ticket->id} aberto — RH notificado e vai resolver (RF10/RF12).");
    }

    // helper de domínio para as views (aulas ativas do usuário)
    public static function aulaAtivaDaInscricao(Inscricao $inscricao): ?Aula
    {
        return $inscricao->aula->estaAtiva()
            && $inscricao->status === 'reservada' ? $inscricao->aula : null;
    }
}
