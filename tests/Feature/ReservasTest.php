<?php

namespace Tests\Feature;

// RF05/RF08/RF09 — reserva nominal por Líder/RH; fila automática; cancelamento promove
use App\Models\Inscricao;
use App\Models\Notificacao;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CriaFixtures;
use Tests\TestCase;

class ReservasTest extends TestCase
{
    use RefreshDatabase;
    use CriaFixtures;

    public function test_lider_reserva_nominal_no_seu_setor(): void
    {
        $setor = $this->criarSetor('Silos');
        $lider = \App\Models\User::factory()->comPapeis('lider')->create(['setor_id' => $setor->id]);
        $this->aceitarTermo($lider);
        $liderado = $this->criarColaborador(['setor_id' => $setor->id]);
        $aula = $this->criarAula(); // agendada, 10 vagas

        $this->actingAs($lider)
            ->post(route('portal.rh.reservas.reservar'), [
                'user_id' => $liderado->id,
                'aula_id' => $aula->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas(Inscricao::class, [
            'aula_id' => $aula->id,
            'user_id' => $liderado->id,
            'status' => 'reservada',
            'reservado_por_id' => $lider->id,
        ]);
        $this->assertDatabaseHas(Notificacao::class, [
            'user_id' => $liderado->id,
            'tipo' => 'inscricao_reservada',
        ]);
    }

    public function test_aula_lotada_manda_para_a_fila_com_posicao(): void
    {
        $rh = $this->criarRh();
        $this->aceitarTermo($rh);
        $aula = $this->criarAula(atributos: ['vagas' => 1]);
        $this->inscreverComStatus($aula, $this->criarColaborador(), 'reservada'); // ocupa a única vaga
        $maisUm = $this->criarColaborador();

        $this->actingAs($rh)
            ->post(route('portal.rh.reservas.reservar'), [
                'user_id' => $maisUm->id,
                'aula_id' => $aula->id,
            ])
            ->assertRedirect();

        $inscricao = Inscricao::where('aula_id', $aula->id)->where('user_id', $maisUm->id)->first();
        $this->assertSame('lista_espera', $inscricao->status);
        $this->assertSame(1, $inscricao->posicao_fila); // RF09: fila automática
        $this->assertDatabaseHas(Notificacao::class, ['user_id' => $maisUm->id, 'tipo' => 'fila_entrada']);
    }

    public function test_cancelamento_da_reserva_promove_fila(): void
    {
        $rh = $this->criarRh();
        $this->aceitarTermo($rh);
        $aula = $this->criarAula(atributos: ['vagas' => 1]);
        $titular = $this->criarColaborador();
        $esperando = $this->criarColaborador();
        $this->inscreverComStatus($aula, $titular, 'reservada');
        $this->inscreverComStatus($aula, $esperando, 'lista_espera', 1);

        $inscricaoTitular = Inscricao::where('aula_id', $aula->id)->where('user_id', $titular->id)->first();

        $this->actingAs($rh)
            ->post(route('portal.rh.reservas.cancelar', $inscricaoTitular))
            ->assertRedirect();

        $this->assertSame('cancelada', $inscricaoTitular->fresh()->status);
        $this->assertSame('reservada',
            Inscricao::where('aula_id', $aula->id)->where('user_id', $esperando->id)->value('status')); // RF09
    }

    public function test_lider_nao_reserva_fora_do_setor(): void
    {
        $setorMeu = $this->criarSetor('Portaria');
        $setorOutro = $this->criarSetor('Almoxarifado');
        $lider = \App\Models\User::factory()->comPapeis('lider')->create(['setor_id' => $setorMeu->id]);
        $this->aceitarTermo($lider);
        $pessoa = $this->criarColaborador(['setor_id' => $setorOutro->id]);
        $aula = $this->criarAula();

        $this->actingAs($lider)
            ->post(route('portal.rh.reservas.reservar'), [
                'user_id' => $pessoa->id,
                'aula_id' => $aula->id,
            ])
            ->assertForbidden(); // RF05: líder só inscreve liderados
    }
}
