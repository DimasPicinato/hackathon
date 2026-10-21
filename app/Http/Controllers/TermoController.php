<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Termo;
use App\Models\TermoAceite;
use Illuminate\Http\Request;

// RF04 — aceite do termo LGPD no primeiro login / nova versão
class TermoController extends Controller
{
    public function mostrar(Request $request)
    {
        $termo = Termo::vigente();

        if ($termo === null) {
            return redirect()->route('portal'); // sem termo publicado: portal normal
        }

        return view('portal.termo', compact('termo'));
    }

    public function aceitar(Request $request)
    {
        $termo = Termo::vigente();

        if ($termo !== null && ! $request->boolean('concordo')) {
            return back()->withErrors(['concordo' => 'É preciso aceitar o termo para acessar os portais (ou usar presença manual).']);
        }

        if ($termo !== null) {
            TermoAceite::query()->firstOrCreate([
                'user_id' => $request->user()->id,
                'termo_id' => $termo->id,
            ], ['ip' => $request->ip()]);
        }

        return redirect()->route('portal');
    }
}
