<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Concerns\EscopoGestao;
use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use App\Models\MotivoHomologacao;
use App\Models\Termo;
use App\Models\TipoTicket;
use Illuminate\Http\Request;

// RF26 — painel de configuração (Admin/RH): motivos, tipos de ticket, termo LGPD, SMTP
class ConfiguracoesController extends Controller
{
    use EscopoGestao;

    public function index(Request $request)
    {
        $this->garantirRh();

        return view('portal.rh.configuracoes', [
            'motivos' => MotivoHomologacao::orderBy('ativo', 'desc')->orderBy('nome')->get(),
            'tipos' => TipoTicket::orderBy('ativo', 'desc')->orderBy('nome')->get(),
            'termos' => Termo::orderByDesc('id')->get(),
            'email' => [
                'mailer' => config('mail.default'),
                'host' => config('mail.mailers.smtp.host'),
                'porta' => config('mail.mailers.smtp.port'),
                'remetente' => config('mail.from.address'),
                'nome' => config('mail.from.name'),
            ],
        ]);
    }

    /** Novo motivo entra no catálogo de homologação manual (alimenta RF27). */
    public function criarMotivo(Request $request)
    {
        $this->garantirRh();

        $dados = $request->validate(['nome' => 'required|string|max:120|unique:motivos_homologacao,nome']);

        $motivo = MotivoHomologacao::create(['nome' => $dados['nome'], 'ativo' => true]);

        Auditoria::registrar('motivo_criado', $motivo, ['nome' => $motivo->nome], $request->user()->id, $request->ip());

        return back()->with('ok', "Motivo \"{$motivo->nome}\" disponível para homologação (RF26).");
    }

    public function alternarMotivo(Request $request, MotivoHomologacao $motivo)
    {
        $this->garantirRh();

        $motivo->update(['ativo' => ! $motivo->ativo]);

        Auditoria::registrar('motivo_alternado', $motivo, ['ativo' => $motivo->ativo], $request->user()->id, $request->ip());

        return back()->with('ok', "Motivo \"{$motivo->nome}\" agora está " . ($motivo->ativo ? 'ativo' : 'inativo') . '.');
    }

    /** RF04 — nova versão do termo re-pede aceite de TODOS no próximo portal. */
    public function publicarTermo(Request $request)
    {
        $this->garantirRh();

        $dados = $request->validate([
            'versao' => 'required|string|max:20|unique:termos,versao',
            'titulo' => 'required|string|max:120',
            'conteudo' => 'required|string',
        ]);

        Termo::query()->update(['ativo' => false]); // antiga deixa de vigir

        $termo = Termo::create([
            'versao' => $dados['versao'],
            'titulo' => $dados['titulo'],
            'conteudo' => $dados['conteudo'],
            'ativo' => true,
        ]);

        Auditoria::registrar('termo_publicado', $termo, ['versao' => $termo->versao], $request->user()->id, $request->ip());

        return back()->with('ok', "Termo {$termo->versao} publicado (RF26) — redirecionamento ao aceite valerá para TODOS (RF04).");
    }

    public function alternarTipo(Request $request, TipoTicket $tipo)
    {
        $this->garantirRh();

        $tipo->update(['ativo' => ! $tipo->ativo]);

        Auditoria::registrar('tipo_ticket_alternado', $tipo, ['ativo' => $tipo->ativo], $request->user()->id, $request->ip());

        return back()->with('ok', "Tipo \"{$tipo->nome}\" agora está " . ($tipo->ativo ? 'ativo' : 'inativo') . '.');
    }
}
