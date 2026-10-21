<?php

namespace App\Console\Commands;

use App\Models\Auditoria;
use App\Models\Aula;
use App\Models\Notificacao;
use Illuminate\Console\Command;

// RF25 — lembrete 24h antes da aula + posição da fila de espera
class EnviarLembretesAula extends Command
{
    protected $signature = 'presenza:lembretes';

    protected $description = 'RF25 — lembretes: aula começa em ~24h (vaga nominal e fila de espera)';

    public function handle(): int
    {
        $aulas = Aula::query()
            ->where('status', 'agendada')
            ->whereBetween('data_inicio', [now()->addHours(23), now()->addHours(25)])
            ->with('treinamento', 'inscricoes')
            ->get();

        foreach ($aulas as $aula) {
            $nomeAula = "{$aula->treinamento->nome} — {$aula->titulo} ({$aula->data_inicio->format('d/m H:i')})";

            foreach ($aula->inscricoes as $inscricao) {
                if ($inscricao->status === 'reservada') {
                    Notificacao::create([
                        'user_id' => $inscricao->user_id,
                        'titulo' => 'Lembrete: sua aula é amanhã',
                        'corpo' => "{$nomeAula}. Local: {$aula->local}. Sua vaga está reservada.",
                        'tipo' => 'lembrete_aula',
                        'link' => '/portal',
                    ]);
                } elseif ($inscricao->status === 'lista_espera') {
                    Notificacao::create([
                        'user_id' => $inscricao->user_id,
                        'titulo' => 'Fila de espera: posição ' . $inscricao->posicao_fila,
                        'corpo' => "{$nomeAula}: você está na posição {$inscricao->posicao_fila} da fila — se alguém cancelar, a vaga é sua automaticamente (RF09).",
                        'tipo' => 'lembrete_fila',
                        'link' => '/portal',
                    ]);
                }
            }
        }

        if ($aulas->isNotEmpty()) {
            Auditoria::registrar('lembretes_enviados', null, [
                'aulas' => $aulas->pluck('id')->all(),
                'notificacoes' => Notificacao::query()->whereIn('tipo', ['lembrete_aula', 'lembrete_fila'])->count(),
            ]);
        }

        $this->info($aulas->count() . ' aula(s) dentro da janela de lembrete (24h).');

        return self::SUCCESS;
    }
}
