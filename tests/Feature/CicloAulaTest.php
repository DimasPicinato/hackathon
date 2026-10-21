<?php

namespace Tests\Feature;

// RF06, RF24 — janela da aula controlada pelo instrutor, RH notificado
use App\Enums\StatusAula;
use App\Models\Aula;
use App\Models\Notificacao;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CriaFixtures;
use Tests\TestCase;

class CicloAulaTest extends TestCase
{
    use RefreshDatabase;
    use CriaFixtures;

    public function test_instrutor_inicia_aula_e_rh_e_notificado(): void
    {
        $instrutor = $this->criarInstrutor();
        $aula = $this->criarAula(StatusAula::Agendada, $instrutor);
        $this->aceitarTermo($instrutor);
        $rh = $this->criarRh();

        $this->actingAs($instrutor)
            ->post(route('portal.aula.iniciar', ['aula' => $aula]))
            ->assertRedirect();

        $aula->refresh();
        $this->assertTrue($aula->estaAtiva()); // janela facial aberta (RF03)
        $this->assertNotNull($aula->iniciada_em);
        $this->assertDatabaseHas(Notificacao::class, [ // RF06: notificação ao RH
            'user_id' => $rh->id,
            'tipo' => 'aula',
        ]);
    }

    public function test_instrutor_encerra_aula_e_rh_e_notificado(): void
    {
        $instrutor = $this->criarInstrutor();
        $aula = $this->criarAula(StatusAula::EmAndamento, $instrutor);
        $this->aceitarTermo($instrutor);
        $rh = $this->criarRh();

        $this->actingAs($instrutor)
            ->post(route('portal.aula.encerrar', ['aula' => $aula]))
            ->assertRedirect();

        $aula->refresh();
        $this->assertSame(StatusAula::Encerrada->value, $aula->status->value);
        $this->assertNotNull($aula->encerrada_em);
        $this->assertDatabaseHas(Notificacao::class, ['user_id' => $rh->id]);
    }

    public function test_instrutor_externo_nao_controla_aula_alheia(): void
    {
        $aula = $this->criarAula(StatusAula::Agendada, $this->criarInstrutor());
        $intruso = $this->criarInstrutor(); // outra pessoa, mesmo papel
        $this->aceitarTermo($intruso);

        $this->actingAs($intruso)
            ->post(route('portal.aula.iniciar', ['aula' => $aula]))
            ->assertForbidden(); // RF06: só o instrutor da aula (ou Admin)
    }

    public function test_encerrar_aula_nao_iniciada_e_recusado(): void
    {
        $instrutor = $this->criarInstrutor();
        $aula = $this->criarAula(StatusAula::Agendada, $instrutor);
        $this->aceitarTermo($instrutor);

        $this->actingAs($instrutor)
            ->post(route('portal.aula.encerrar', ['aula' => $aula]))
            ->assertSessionHasErrors('aula');
    }
}
