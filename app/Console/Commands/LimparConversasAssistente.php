<?php

namespace App\Console\Commands;

use App\Models\AssistenteMensagem;
use Illuminate\Console\Command;

// LGPD/Q58 — retenção das conversas do assistente (IP é dado pessoal): apaga
// registros com mais de 30 dias (decisão do time, 17/11/2026).
class LimparConversasAssistente extends Command
{
    protected $signature = 'presenza:limpar-assistente';

    protected $description = 'LGPD — apaga conversas do assistente (RF33) com mais de 30 dias';

    public function handle(): int
    {
        $corte = now()->subDays(30);

        $apagadas = AssistenteMensagem::where('created_at', '<', $corte)->delete();

        $this->info("Assistente: {$apagadas} conversa(s) anterior(es) a {$corte->format('d/m/Y')} apagada(s).");

        return self::SUCCESS;
    }
}
