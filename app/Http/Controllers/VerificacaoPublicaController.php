<?php

namespace App\Http\Controllers;

use App\Enums\ResultadoVerificacao;
use App\Models\Certificado;
use App\Models\Verificacao;
use Barryvdh\DomPDF\Facade\Pdf;
use chillerlan\QRCode\QRCode;
use Illuminate\Http\Request;

// RF21/RF22 — página pública: QR/código/link, mínimo de dados (LGPD), PDF
class VerificacaoPublicaController extends Controller
{
    public function busca(Request $request)
    {
        if ($q = trim((string) $request->input('q'))) {
            return redirect()->route('verificar.mostrar', ['codigo' => $q]);
        }

        return view('verificar.busca');
    }

    public function mostrar(Request $request, string $codigo)
    {
        $codigo = trim($codigo);
        $certificado = Certificado::porCodigo($codigo);
        $resultado = $certificado
            ? $certificado->resultadoVerificacao()
            : ResultadoVerificacao::NaoEncontrado;

        Verificacao::create([ // log mínimo (RNF01)
            'codigo' => $codigo,
            'resultado' => $resultado->value,
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);

        if (! $certificado) {
            return view('verificar.resultado', [
                'codigo' => $codigo,
                'resultado' => $resultado,
                'certificado' => null,
                'qr' => null,
            ]);
        }

        $certificado->load(['user', 'treinamento.aulas.instrutor']);

        $instrutores = $certificado->treinamento->aulas
            ->pluck('instrutor.name')->filter()->unique()->implode(', ');

        $qr = null;
        try {
            $qr = (new QRCode)->render(url('/verificar/' . $codigo));
        } catch (\Throwable) {
            $qr = null; // QR é opcional — o link sempre funciona (RNF06)
        }

        return view('verificar.resultado', [
            'codigo' => $codigo,
            'resultado' => $resultado,
            'certificado' => $certificado,
            'instrutores' => $instrutores,
            'qr' => $qr,
        ]);
    }

    // Download do PDF — mesmo conteúdo mínimo da página (RF21)
    public function pdf(Request $request, string $codigo)
    {
        $certificado = Certificado::porCodigo(trim($codigo));

        if (! $certificado || $certificado->substituido_por_id !== null) {
            abort(404, 'Certificado não localizado.');
        }

        $certificado->load(['user', 'treinamento.aulas.instrutor', 'emitidoPor']);

        return Pdf::loadView('verificar.pdf', [
                'certificado' => $certificado,
                'instrutores' => $certificado->treinamento->aulas->pluck('instrutor.name')
                    ->filter()->unique()->implode(', '),
            ])
            ->setPaper('a4', 'portrait')
            ->stream("certificado-{$certificado->codigo}.pdf");
    }
}
