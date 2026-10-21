<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Concerns\EscopoGestao;
use App\Http\Controllers\Controller;
use App\Models\Aula;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

// RF28 — relatórios: por sessão e por pessoa, com exportação CSV
class RelatoriosController extends Controller
{
    use EscopoGestao;

    public function index(Request $request)
    {
        $this->garantirGestor();

        $aulas = Aula::with(['treinamento', 'inscricoes.user.setor', 'presencas'])
            ->orderByDesc('data_inicio')->get();

        $resumo = $aulas->map(fn (Aula $aula) => [
            'aula' => $aula,
            'convocados' => $aula->inscricoes->where('status', '!=', 'cancelada')->count(),
            'presentes' => $aula->presencas->count(),
            'faltas' => max(0, $aula->inscricoes->where('status', '!=', 'cancelada')->count() - $aula->presencas->count()),
        ]);

        return view('portal.rh.relatorios', ['resumo' => $resumo]);
    }

    public function csvSessao(Request $request, Aula $aula): StreamedResponse
    {
        $this->garantirGestor();

        $aula->load(['treinamento', 'inscricoes.user.setor', 'presencas']);

        $nomeArquivo = 'presenza-sessao-' . $aula->id . '.csv';

        return response()->streamDownload(function () use ($aula) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM: Excel/Excel PT entende acentuação
            fputcsv($out, ['Presenza — relatório da sessão (RF28)'], ';');
            fputcsv($out, ['Treinamento', $aula->treinamento->nome], ';');
            fputcsv($out, ['Aula', $aula->titulo, 'Ordem', $aula->ordem], ';');
            fputcsv($out, ['Data', $aula->data_inicio->format('d/m/Y H:i'), 'Local', $aula->local, 'Status', $aula->status->value], ';');
            fputcsv($out, [], ';');
            fputcsv($out, ['Nome', 'Matrícula', 'Setor', 'Inscrição', 'Presença', 'Método', 'Motivo (homologação)', 'Registrada em'], ';');

            foreach ($aula->inscricoes as $inscricao) {
                $presenca = $aula->presencas->firstWhere('user_id', $inscricao->user_id);

                fputcsv($out, [
                    $inscricao->user->name,
                    $inscricao->user->matricula,
                    $inscricao->user->setor?->nome,
                    $inscricao->status,
                    $presenca ? 'presente' : 'ausente',
                    $presenca?->metodo?->value,
                    $presenca?->motivo?->nome,
                    $presenca?->created_at?->format('d/m/Y H:i'),
                ], ';');
            }

            fclose($out);
        }, $nomeArquivo, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function csvPessoas(Request $request): StreamedResponse
    {
        $this->garantirGestor();

        $pessoas = User::query()->where('ativo', true)
            ->whereJsonContains('papeis', 'colaborador')
            ->with(['setor', 'presencas', 'certificados'])
            ->orderBy('name')->get();

        if ($request->user()->temPapel('lider')) {
            $pessoas = $pessoas->where('setor_id', $request->user()->setor_id)->values();
        }

        return response()->streamDownload(function () use ($pessoas) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Presenza — histórico por pessoa (RF28)'], ';');
            fputcsv($out, ['Nome', 'Matrícula', 'Setor', 'Turno', 'Presenças', 'Certificados vigentes', 'Válidos até'], ';');

            foreach ($pessoas as $pessoa) {
                $certificados = $pessoa->certificados->filter(fn ($c) => $c->substituido_por_id === null);

                fputcsv($out, [
                    $pessoa->name,
                    $pessoa->matricula,
                    $pessoa->setor?->nome,
                    $pessoa->turno,
                    $pessoa->presencas->count(),
                    $certificados->pluck('codigo')->implode(', '),
                    $certificados->sortByDesc('valido_ate')->first()?->valido_ate?->format('d/m/Y'),
                ], ';');
            }

            fclose($out);
        }, 'presenza-pessoas.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
