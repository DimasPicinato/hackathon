<?php

namespace App\Http\Controllers;

use App\Models\AssistenteMensagem;
use App\Services\RuntimeAssistente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

/**
 * RF29–RF34 — Assistente público do Presenza.
 *
 * - responder(): rota pública (sem login, sem CSRF) usada pelo widget;
 * - conversas(): visão do Admin com o registro de auditoria (RF33).
 */
class AssistenteController extends Controller
{
    /** Quantidade de mensagens por página na visão do Admin. */
    private const POR_PAGINA = 40;

    public function responder(Request $request, RuntimeAssistente $runtime): JsonResponse
    {
        $dados = $request->validate([
            'sessao' => 'required|string|max:64',
            'pergunta' => 'required|string|min:3|max:500',
        ]);

        $estado = 'ok';

        try {
            $resposta = $runtime->responder($dados['sessao'], $dados['pergunta']);
        } catch (Throwable $e) {
            report($e);
            $estado = 'fallback';
            $resposta = RuntimeAssistente::CORTESIA; // RNF08 — demo nunca para
        }

        AssistenteMensagem::create([
            'sessao' => $dados['sessao'],
            'pergunta' => $dados['pergunta'],
            'resposta' => $resposta,
            'estado' => $estado,
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'resposta' => $resposta,
            'estado' => $estado,
        ]);
    }

    /** RF33 — conversas registradas, visíveis apenas ao Admin. */
    public function conversas(Request $request)
    {
        // RF33: registro de auditoria é exclusivo do Admin (nem RH acessa).
        abort_unless($request->user()?->temPapel('admin'), 403);

        return view('portal.rh.assistente', [
            'mensagens' => AssistenteMensagem::orderByDesc('id')->paginate(self::POR_PAGINA),
        ]);
    }
}
