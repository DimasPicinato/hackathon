<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Aula;
use App\Models\User;
use App\Services\PresencaService;
use Illuminate\Http\Request;

// RF14/RF15 — chamada por presença facial (autônoma e via instrutor) e homologação manual
class ChamadaController extends Controller
{
    public function __construct(private PresencaService $presenca)
    {
    }

    // RF14.a — o colaborador valida a própria presença pela câmera (embedding do navegador)
    public function facialAutonoma(Request $request)
    {
        // form HTML chega com o vetor como string JSON; API JSON chega como array
        $request->merge(['embedding' => $this->decodificarEmbedding($request->input('embedding'))]);

        $data = $request->validate([
            'aula_id' => ['required', 'integer', 'exists:aulas,id'],
            'embedding' => ['required', 'array', 'size:128'],
            'embedding.*' => ['numeric', 'between:-2,2'],
        ]);

        $aula = Aula::findOrFail($data['aula_id']);
        $this->presenca->registrarFacial($aula, $request->user(), null, $request->ip(), $data['embedding']);

        return redirect()->route('portal')
            ->with('status', "Presença registrada por reconhecimento facial em '{$aula->titulo}'.");
    }

    /** RF14.b — instrutor executa o reconhecimento para o colaborador */
    public function facialViaInstrutor(Request $request, Aula $aula)
    {
        $this->autorizarInstrutor($aula);

        $request->merge(['embedding' => $this->decodificarEmbedding($request->input('embedding'))]);

        $data = $request->validate([
            'colaborador_id' => ['required', 'integer', 'exists:users,id'],
            'embedding' => ['required', 'array', 'size:128'],
            'embedding.*' => ['numeric', 'between:-2,2'],
        ]);
        $colaborador = User::findOrFail($data['colaborador_id']);

        $this->presenca->registrarFacial($aula, $colaborador, $aula->instrutor_id, $request->ip(), $data['embedding']);

        return redirect()->route('portal.aula.mostrar', $aula)
            ->with('status', "Presença de {$colaborador->name} registrada por reconhecimento facial.");
    }

    /** O vetor facial chega como string JSON (form HTML) ou array (API JSON). */
    private function decodificarEmbedding(mixed $valor): mixed
    {
        if (is_string($valor)) {
            $decodificado = json_decode($valor, true);

            return is_array($decodificado) ? $decodificado : [];
        }

        return $valor;
    }

    // RF15 — homologação manual: senha do colaborador + motivo do catálogo (ou novo)
    public function manual(Request $request, Aula $aula)
    {
        $this->autorizarInstrutor($aula);

        $data = $request->validate([
            'colaborador_id' => ['required', 'integer', 'exists:users,id'],
            'motivo_id' => ['nullable', 'integer', 'exists:motivos_homologacao,id'],
            'motivo_novo' => ['nullable', 'string', 'max:120'],
            'senha' => ['required', 'string'],
        ]);

        $colaborador = User::findOrFail($data['colaborador_id']);

        $this->presenca->registrarManual(
            $aula,
            $colaborador,
            $request->user(),
            $data['motivo_id'] ?? null,
            trim((string) ($data['motivo_novo'] ?? '')) ?: null,
            $data['senha'],
            $request->ip(),
        );

        return redirect()->route('portal.aula.mostrar', $aula)
            ->with('status', "Presença de {$colaborador->name} HOMOLOGADA manualmente (com senha e motivo).");
    }

    // Aula do instrutor: tela da chamada
    public function aula(Request $request, Aula $aula)
    {
        $this->autorizarInstrutor($aula);

        $aula->load(['treinamento', 'instrutor']);
        $inscritos = $aula->inscricoes()->with('user')->orderBy('status')->get();
        $presentes = $aula->presencas()->with(['user', 'motivo'])->get();
        $motivos = \App\Models\MotivoHomologacao::query()->where('ativo', true)->orderBy('nome')->get();

        return view('portal.aula', compact('aula', 'inscritos', 'presentes', 'motivos'));
    }

    private function autorizarInstrutor(Aula $aula): void
    {
        $user = request()->user();
        $ehInstrutorDona = $aula->instrutor_id === $user->id;
        $ehGestor = $user->temPapel('admin') || $user->temPapel('rh');

        if (! $ehInstrutorDona && ! $ehGestor) {
            abort(403, 'Aula não é sua (RF06: instrutor conduz apenas suas aulas).');
        }
    }
}
