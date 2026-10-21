<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Concerns\EscopoGestao;
use App\Http\Controllers\Controller;
use App\Models\Certificado;
use App\Models\Treinamento;
use App\Models\User;
use App\Services\CertificadoService;
use Illuminate\Http\Request;

// RF18–RF20 — lista de elegíveis, emissão manual pelo RH e reemissão imutável
class CertificadosController extends Controller
{
    use EscopoGestao;

    public function index(Request $request)
    {
        $this->garantirGestor();

        $treinamentos = Treinamento::with(['aulas.presencas', 'certificados.user'])
            ->orderBy('nome')->get();

        $colaboradores = User::query()->where('ativo', true)
            ->whereJsonContains('papeis', 'colaborador')
            ->orderBy('name')->get();

        $elegiveisPorTreinamento = $treinamentos->mapWithKeys(function ($treinamento) use ($colaboradores) {
            return [$treinamento->id => $colaboradores
                ->filter(fn ($u) => CertificadoService::elegivel($u, $treinamento))
                ->values()];
        });

        return view('portal.rh.certificados', [
            'treinamentos' => $treinamentos,
            'elegiveisPorTreinamento' => $elegiveisPorTreinamento,
            'podeEmitir' => $request->user()->temPapel('rh') || $request->user()->temPapel('admin'),
        ]);
    }

    public function emitir(Request $request)
    {
        $this->garantirRh();

        $dados = $request->validate([
            'user_id' => 'required|exists:users,id',
            'treinamento_id' => 'required|exists:treinamentos,id',
        ]);

        $certificado = CertificadoService::emitir(
            User::findOrFail($dados['user_id']),
            Treinamento::findOrFail($dados['treinamento_id']),
            $request->user(),
            $request->ip(),
        );

        return back()->with('ok', "Certificado {$certificado->codigo} emitido (RF19) — verificável no QR público.");
    }

    public function reemitir(Request $request, Certificado $certificado)
    {
        $this->garantirRh();

        $novo = CertificadoService::reemitir($certificado, $request->user(), $request->ip());

        return back()->with('ok', "Reemissão (RF20): {$certificado->codigo} → {$novo->codigo} (v{$novo->versao}). O código antigo deixa de verificar.");
    }
}
