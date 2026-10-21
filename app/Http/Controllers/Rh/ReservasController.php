<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Concerns\EscopoGestao;
use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use App\Models\Aula;
use App\Models\Inscricao;
use App\Models\Notificacao;
use App\Models\User;
use App\Services\VagaService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

// RF05/RF08/RF09 — Líder/RH reservam vagas nominais; fila automática quando lota
class ReservasController extends Controller
{
    use EscopoGestao;

    public function index(Request $request)
    {
        $this->garantirGestor();

        $aulas = Aula::with(['treinamento', 'inscricoes.user'])
            ->where('status', 'agendada')
            ->orderBy('data_inicio')->get();

        $pessoas = User::query()->where('ativo', true)
            ->whereJsonContains('papeis', 'colaborador')
            ->with('setor')->orderBy('name')->get();

        if ($request->user()->temPapel('lider')) {
            $pessoas = $pessoas->where('setor_id', $request->user()->setor_id)->values();
        }

        return view('portal.rh.reservas', [
            'aulas' => $aulas,
            'pessoas' => $pessoas,
        ]);
    }

    public function reservar(Request $request)
    {
        $this->garantirGestor();

        $dados = $request->validate([
            'user_id' => 'required|exists:users,id',
            'aula_id' => 'required|exists:aulas,id',
        ]);

        $aula = Aula::with('treinamento')->findOrFail($dados['aula_id']);
        $pessoa = User::findOrFail($dados['user_id']);
        $this->autorizarSobre($pessoa);

        /** @var Inscricao|null $inscricao */
        $inscricao = $aula->inscricoes()->where('user_id', $pessoa->id)->first();

        if ($inscricao && $inscricao->status !== 'cancelada') {
            return back()->with('ok', "{$pessoa->name} já está {$inscricao->status} nesta aula.");
        }

        if ($aula->vagasRestantes() > 0) {
            Inscricao::updateOrCreate(
                ['aula_id' => $aula->id, 'user_id' => $pessoa->id],
                ['status' => 'reservada', 'posicao_fila' => null, 'reservado_por_id' => $request->user()->id],
            );
            $resultado = "Vaga nominal reservada (RF05/RF08) — {$aula->treinamento->nome}, {$aula->titulo}.";

            Notificacao::create([
                'user_id' => $pessoa->id,
                'titulo' => 'Você foi inscrito(a) em uma aula',
                'corpo' => "{$aula->treinamento->nome} — {$aula->titulo} em {$aula->data_inicio->format('d/m H:i')} (vaga nominal feita pela gestão).",
                'tipo' => 'inscricao_reservada',
            ]);
        } else {
            $posicao = ($aula->inscricoes()->where('status', 'lista_espera')->max('posicao_fila') ?? 0) + 1;
            Inscricao::updateOrCreate(
                ['aula_id' => $aula->id, 'user_id' => $pessoa->id],
                ['status' => 'lista_espera', 'posicao_fila' => $posicao, 'reservado_por_id' => $request->user()->id],
            );
            $resultado = "Aula lotada — {$pessoa->name} entrou na fila de espera (posição {$posicao}), RF09.";

            Notificacao::create([
                'user_id' => $pessoa->id,
                'titulo' => "Você entrou na fila de espera (posição {$posicao})",
                'corpo' => "{$aula->treinamento->nome} — {$aula->titulo}: se abrir vaga, será promovido(a) automaticamente (RF09).",
                'tipo' => 'fila_entrada',
            ]);
        }

        Auditoria::registrar('reserva_feita', $aula, [
            'pessoa' => $pessoa->matricula,
            'por' => $request->user()->matricula,
        ], $request->user()->id, $request->ip());

        return back()->with('ok', $resultado);
    }

    /** Cancela a vaga nominal de alguém e promove a fila (RF09). */
    public function cancelar(Request $request, Inscricao $inscricao)
    {
        $this->garantirGestor();
        $this->autorizarSobre($inscricao->user);

        if ($inscricao->status === 'cancelada') {
            throw ValidationException::withMessages(['inscricao' => 'Inscrição já está cancelada.']);
        }

        $aula = Aula::with('treinamento')->findOrFail($inscricao->aula_id);
        $inscricao->update(['status' => 'cancelada', 'posicao_fila' => null]);

        app(VagaService::class)->promoverDaFila($aula, $request->user()->id, $request->ip());

        Notificacao::create([
            'user_id' => $inscricao->user_id,
            'titulo' => 'Reserva cancelada pela gestão',
            'corpo' => "{$aula->treinamento->nome} — {$aula->titulo}.",
            'tipo' => 'inscricao_cancelada',
        ]);

        return back()->with('ok', "Reserva de {$inscricao->user->name} cancelada; fila promovida (RF09).");
    }
}
