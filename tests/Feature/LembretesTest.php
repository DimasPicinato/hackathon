<?php

namespace Tests\Feature;

// RF25 — lembrete 24h antes da aula; fila avisada da posição (e RF09 a promove depois)
use App\Models\Notificacao;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\Concerns\CriaFixtures;
use Tests\TestCase;

class LembretesTest extends TestCase
{
    use RefreshDatabase;
    use CriaFixtures;

    public function test_lembrete_bate_na_porta_24h_antes(): void
    {
        $aula24h = $this->criarAula(\App\Enums\StatusAula::Agendada, atributos: [
            'data_inicio' => now()->addDay(),
        ]);
        $aulaFuraJanela = $this->criarAula(\App\Enums\StatusAula::Agendada, atributos: [
            'data_inicio' => now()->addDays(5),
            'ordem' => 2,
        ], instrutor: null);
        $comVaga = $this->criarColaborador();
        $naFila = $this->criarColaborador();
        $this->inscreverComStatus($aula24h, $comVaga, 'reservada');
        $this->inscreverComStatus($aula24h, $naFila, 'lista_espera', 1);
        $this->inscreverComStatus($aulaFuraJanela, $comVaga, 'reservada'); // não deve receber

        Artisan::call('presenza:lembretes');

        $this->assertDatabaseHas(Notificacao::class, [
            'user_id' => $comVaga->id,
            'tipo' => 'lembrete_aula',
        ]);
        $this->assertDatabaseHas(Notificacao::class, [
            'user_id' => $naFila->id,
            'tipo' => 'lembrete_fila',
        ]);
        // fora da janela de 24h, nenhum lembrete a mais
        $this->assertSame(1, Notificacao::where('tipo', 'lembrete_aula')->count());
    }

    public function test_aula_em_andamento_nao_recebe_lembrete(): void
    {
        $aulaAtiva = $this->criarAula(\App\Enums\StatusAula::EmAndamento, $this->criarInstrutor(), [
            'data_inicio' => now()->addHours(24), // dentro da janela MAS já em andamento
        ]);
        $aluno = $this->criarColaborador();
        $this->inscreverComStatus($aulaAtiva, $aluno, 'reservada');

        Artisan::call('presenza:lembretes');

        $this->assertDatabaseMissing(Notificacao::class, ['user_id' => $aluno->id, 'tipo' => 'lembrete_aula']);
    }
}
