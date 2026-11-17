<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Enums\StatusAula;
use App\Models\Aula;
use App\Models\Notificacao;
use App\Models\User;
use App\Services\PresencaService;
use Illuminate\Http\Request;

// RF06 — instrutor inicia/encerra a AUla; cada ação notifica RH (RF24)
class AulaController extends Controller
{
    public function iniciar(Request $request, Aula $aula)
    {
        $this->autorizar($aula);

        if ($aula->status === StatusAula::Encerrada) {
            return back()->withErrors(['aula' => 'Aula já encerrada.']);
        }

        $aula->update([
            'status' => StatusAula::EmAndamento->value,
            'iniciada_em' => now(),
        ]);

        $this->notificarRh($aula, 'Aula iniciada', "{$aula->treinamento->nome} — {$aula->titulo}: janela de presença facial ABERTA.");

        return back()->with('status', "Aula iniciada — o reconhecimento facial está liberado para '{$aula->titulo}'.");
    }

    public function encerrar(Request $request, Aula $aula)
    {
        $this->autorizar($aula);

        if ($aula->status !== StatusAula::EmAndamento) {
            return back()->withErrors(['aula' => 'Somente aulas em andamento podem ser encerradas.']);
        }

        $aula->update([
            'status' => StatusAula::Encerrada->value,
            'encerrada_em' => now(),
        ]);

        $this->notificarRh($aula, 'Aula encerrada', "{$aula->treinamento->nome} — {$aula->titulo}: chamada fechada. Fora da janela, presença somente por registro manual.");

        return back()->with('status', "Aula encerrada — o RH foi notificado.");
    }

    private function autorizar(Aula $aula): void
    {
        $user = request()->user();

        if ($aula->instrutor_id !== $user->id && ! $user->temPapel('admin')) {
            abort(403, 'Esta aula não é conduzida por você.');
        }
    }

    private function notificarRh(Aula $aula, string $titulo, string $corpo): void
    {
        User::query()->whereJsonContains('papeis', 'rh')->get()->each(function (User $rh) use ($aula, $titulo, $corpo): void {
            Notificacao::create([
                'user_id' => $rh->id,
                'titulo' => $titulo,
                'corpo' => $corpo,
                'tipo' => 'aula',
                'link' => route('portal.aula.mostrar', $aula, absolute: false),
            ]);
        });
    }
}
