<?php

namespace App\Http\Controllers\Rh;

use App\Enums\StatusAula;
use App\Http\Controllers\Concerns\EscopoGestao;
use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use App\Models\Aula;
use App\Models\Setor;
use App\Models\Treinamento;
use Illuminate\Http\Request;

// RF07 — RH/Admin criam/editam treinamentos e aulas; Líder apenas no escopo do próprio setor
class TreinamentosController extends Controller
{
    use EscopoGestao;

    public function index(Request $request)
    {
        $this->garantirGestor();

        $treinamentos = Treinamento::with(['aulas.inscricoes', 'setor'])->orderBy('nome')->get();

        if ($this->soLider($request)) {
            $treinamentos = $treinamentos->where('setor_id', $request->user()->setor_id)->values();
        }

        return view('portal.rh.treinamentos', [
            'treinamentos' => $treinamentos,
            'setores' => Setor::orderBy('nome')->get(),
        ]);
    }

    public function criar(Request $request)
    {
        $this->garantirGestor();

        $dados = $request->validate([
            'nome' => 'required|string|max:150|unique:treinamentos,nome',
            'descricao' => 'nullable|string|max:2000',
            'carga_horaria' => 'required|integer|min:1|max:500',
            'validade_meses' => 'nullable|integer|min:1|max:120',
            'setor_id' => 'nullable|exists:setores,id',
        ]);

        if ($this->soLider($request)) { // RF07: líder cria exclusivamente para o seu setor
            $dados['setor_id'] = $request->user()->setor_id;
        }

        $treinamento = Treinamento::create($dados);

        Auditoria::registrar('treinamento_criado', $treinamento, [
            'nome' => $treinamento->nome,
        ], $request->user()->id, $request->ip());

        return back()->with('ok', "Treinamento \"{$treinamento->nome}\" criado — adicione as aulas abaixo (RF07).");
    }

    public function atualizar(Request $request, Treinamento $treinamento)
    {
        $this->garantirGestor();
        $this->autorizarTreinamento($request, $treinamento);

        $dados = $request->validate([
            'nome' => 'required|string|max:150|unique:treinamentos,nome,' . $treinamento->id,
            'descricao' => 'nullable|string|max:2000',
            'carga_horaria' => 'required|integer|min:1|max:500',
            'validade_meses' => 'nullable|integer|min:1|max:120',
        ]);

        $treinamento->update($dados);

        Auditoria::registrar('treinamento_ajustado', $treinamento, $dados, $request->user()->id, $request->ip());

        return back()->with('ok', "Treinamento \"{$treinamento->nome}\" atualizado (RF07).");
    }

    public function criarAula(Request $request, Treinamento $treinamento)
    {
        $this->garantirGestor();
        $this->autorizarTreinamento($request, $treinamento);

        $dados = $request->validate([
            'titulo' => 'required|string|max:150',
            'data_inicio' => 'required|date',
            'vagas' => 'required|integer|min:1|max:300',
            'local' => 'nullable|string|max:120',
        ]);

        $aula = Aula::create([
            'treinamento_id' => $treinamento->id,
            'ordem' => ($treinamento->aulas()->max('ordem') ?? 0) + 1,
            'status' => 'agendada',
            'instrutor_id' => null,
            ...$dados,
        ]);

        Auditoria::registrar('aula_criada', $aula, [
            'treinamento' => $treinamento->nome,
        ], $request->user()->id, $request->ip());

        return back()->with('ok', "Aula {$aula->ordem} de \"{$treinamento->nome}\" criada — instrutor assume pela tela própria (RF06).");
    }

    public function atualizarAula(Request $request, Aula $aula)
    {
        $this->garantirGestor();
        $this->autorizarTreinamento($request, $aula->treinamento);

        abort_if($aula->status !== StatusAula::Agendada, 422,
            'Aula em andamento/encerrada não é re-agendada aqui — a janela é controlada pelo instrutor (RF06).');

        $dados = $request->validate([
            'titulo' => 'required|string|max:150',
            'data_inicio' => 'required|date',
            'vagas' => 'required|integer|min:1|max:300',
            'local' => 'nullable|string|max:120',
        ]);

        $aula->update($dados);

        Auditoria::registrar('aula_ajustada', $aula, $dados, $request->user()->id, $request->ip());

        return back()->with('ok', "Aula {$aula->ordem} ajustada (RF07).");
    }

    private function soLider(Request $request): bool
    {
        $u = $request->user();

        return $u->temPapel('lider') && ! ($u->temPapel('rh') || $u->temPapel('admin'));
    }

    private function autorizarTreinamento(Request $request, Treinamento $treinamento): void
    {
        $u = $request->user();

        if ($u->temPapel('rh') || $u->temPapel('admin')) {
            return;
        }

        if ($u->temPapel('lider')
            && $treinamento->setor_id !== null
            && $treinamento->setor_id === $u->setor_id) {
            return;
        }

        abort(403, 'Fora do seu escopo (RF07).');
    }
}
