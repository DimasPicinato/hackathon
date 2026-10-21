<?php

namespace App\Http\Controllers\Concerns;

use App\Models\User;

// RF05/RF12 — RH/Admin agem em tudo; Líder só dentro do próprio setor
trait EscopoGestao
{
    /** Área de gestão: RH, Líder ou Admin. */
    protected function garantirGestor(): void
    {
        $u = request()->user();

        if (! ($u?->temPapel('rh') || $u?->temPapel('admin') || $u?->temPapel('lider'))) {
            abort(403, 'Área de gestão: RH, Líder ou Admin.');
        }
    }

    /** Apenas RH/Admin (tabela de certificados RF19 é exclusiva do RH). */
    protected function garantirRh(): void
    {
        $u = request()->user();

        if (! ($u?->temPapel('rh') || $u?->temPapel('admin'))) {
            abort(403, 'Emissão de certificados é exclusiva do RH (RF19).');
        }
    }

    /** Líder alcança apenas liderados do seu setor; RH/Admin alcançam todos. */
    protected function autorizarSobre(?User $alvo): void
    {
        $u = request()->user();

        if ($u->temPapel('rh') || $u->temPapel('admin')) {
            return;
        }

        if ($u->temPapel('lider')
            && $alvo !== null
            && $alvo->setor_id !== null
            && $alvo->setor_id === $u->setor_id) {
            return;
        }

        abort(403, 'Fora do seu escopo: Líder atua apenas nos liderados (RF12).');
    }
}
