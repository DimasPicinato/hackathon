<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\BiometriaService;
use Illuminate\Http\Request;

// RF03/RNF01 — autocadastro biométrico (enrollment): o colaborador registra o
// PRÓPRIO rosto pelo navegador (imagem nunca sai do dispositivo; só o embedding
// de 128 dimensões chega ao servidor). Exige termo LGPD vigente (RF04).
class BiometriaController extends Controller
{
    public function __construct(private BiometriaService $biometria)
    {
    }

    /** Tela de autocadastro com status do rosto atual. */
    public function mostrar(Request $request)
    {
        $atual = $request->user()->biometria;

        return view('portal.biometria', [
            'atual' => $atual,
            'comCadastroReal' => $atual && $atual->modelo === BiometriaService::MODELO,
        ]);
    }

    /** Grava o embedding capturado (sobrescreve o anterior). */
    public function cadastrar(Request $request)
    {
        // form HTML chega com o vetor como string JSON; API JSON chega como array
        $bruto = $request->input('embedding');
        $request->merge(['embedding' => is_string($bruto) ? json_decode($bruto, true) : $bruto]);

        $data = $request->validate([
            'embedding' => ['required', 'array', 'size:128'],
            'embedding.*' => ['numeric', 'between:-2,2'],
        ]);

        $biometria = $this->biometria->cadastrar($request->user(), $data['embedding'], null, $request->ip());

        return redirect()->route('portal.biometria.mostrar')
            ->with('status', 'Rosto cadastrado com sucesso — presença por reconhecimento facial liberada.');
    }

    /** Teste rápido de reconhecimento: compara o rosto capturado com o próprio cadastro. */
    public function testar(Request $request)
    {
        $bruto = $request->input('embedding');
        $request->merge(['embedding' => is_string($bruto) ? json_decode($bruto, true) : $bruto]);

        $data = $request->validate([
            'embedding' => ['required', 'array', 'size:128'],
            'embedding.*' => ['numeric', 'between:-2,2'],
        ]);

        $user = $request->user();
        if (! $this->biometria->temCadastroReal($user)) {
            return response()->json([
                'ok' => false,
                'reconhecido' => false,
                'similaridade' => null,
                'mensagem' => 'Você ainda não tem um rosto cadastrado para comparar. Faça o cadastro primeiro.',
            ], 422);
        }

        $similaridade = $this->biometria->similaridadeCom($user, $data['embedding']);
        $limiar = $this->biometria->limiar();
        $reconhecido = $similaridade !== null && $similaridade >= $limiar;

        return response()->json([
            'ok' => true,
            'reconhecido' => $reconhecido,
            'similaridade' => $similaridade,
            'mensagem' => $reconhecido
                ? 'Reconhecimento confirmado: seu rosto confere com o cadastro.'
                : 'O rosto capturado não confere com o seu cadastro. Tente em um ambiente mais iluminado, olhando diretamente para a câmera.',
        ]);
    }
}
