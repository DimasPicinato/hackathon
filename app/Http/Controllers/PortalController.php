<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Aula;
use App\Models\Certificado;
use App\Models\Inscricao;
use App\Models\Justificativa;
use App\Models\Ticket;
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

    // helper de domínio para as views (aulas ativas do usuário)
    public static function aulaAtivaDaInscricao(Inscricao $inscricao): ?Aula
    {
        return $inscricao->aula->estaAtiva()
            && $inscricao->status === 'reservada' ? $inscricao->aula : null;
    }
}
