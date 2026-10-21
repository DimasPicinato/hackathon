<?php

namespace App\Services;

use App\Models\Auditoria;
use App\Models\Aula;
use App\Models\Notificacao;
use App\Models\Treinamento;
use App\Models\User;

// RF09 — fila de espera automática; promoção quando vaga é liberada
class VagaService
{
    /** Devolve as vagas de um colaborador em TODAS as aulas do treinamento (RF12/RF10). */
    public function devolverVagas(User $colaborador, Treinamento $treinamento, ?int $atorId = null, ?string $ip = null): int
    {
        $liberadas = 0;

        foreach ($treinamento->aulas()->with('inscricoes')->get() as $aula) {
            $inscricao = $aula->inscricoes->firstWhere('user_id', $colaborador->id);

            if ($inscricao && $inscricao->status !== 'cancelada') {
                $inscricao->update(['status' => 'cancelada', 'posicao_fila' => null]);
                $this->promoverDaFila($aula, $atorId, $ip);
                $liberadas++;
            }
        }

        return $liberadas;
    }

    /** RF09: quem lidera a fila ocupa a vaga aberta, na ordem dos pedidos. */
    public function promoverDaFila(Aula $aula, ?int $atorId = null, ?string $ip = null): void
    {
        if ($aula->vagasRestantes() <= 0) {
            return;
        }

        $proxima = $aula->inscricoes()
            ->where('status', 'lista_espera')
            ->orderBy('posicao_fila')->orderBy('id')
            ->first();

        if (! $proxima) {
            return;
        }

        $proxima->update(['status' => 'reservada', 'posicao_fila' => null]);

        // reordena o resto da fila (cala a posição)
        $aula->inscricoes()
            ->where('status', 'lista_espera')
            ->orderBy('posicao_fila')->orderBy('id')
            ->get()
            ->each(fn ($insc, $i) => $insc->update(['posicao_fila' => $i + 1]));

        Notificacao::create([
            'user_id' => $proxima->user_id,
            'titulo' => 'Você foi promovido(a) da fila de espera!',
            'corpo' => "{$aula->treinamento->nome} — {$aula->titulo}: abriu uma vaga e você assumiu automaticamente (RF09).",
            'tipo' => 'fila_promocao',
        ]);

        Auditoria::registrar('fila_promovida', $aula, [
            'promovido' => $proxima->user->matricula,
        ], $atorId, $ip);
    }
}
