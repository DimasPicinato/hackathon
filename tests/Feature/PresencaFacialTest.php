<?php

namespace Tests\Feature;

// RF03, RF14, RF16, RF04 — chamada por reconhecimento facial
use App\Enums\StatusAula;
use App\Models\Auditoria;
use App\Models\Aula;
use App\Models\Presenca;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CriaFixtures;
use Tests\TestCase;

class PresencaFacialTest extends TestCase
{
    use RefreshDatabase;
    use CriaFixtures;

    public function test_colaborador_aponta_a_propria_presenca_com_aula_ativa(): void
    {
        $aula = $this->criarAula(StatusAula::EmAndamento, $this->criarInstrutor());
        $colaborador = $this->criarColaborador();
        $this->aceitarTermo($colaborador);

        $this->actingAs($colaborador)
            ->post(route('portal.presenca.facial', ['aula_id' => $aula->id]))
            ->assertRedirect(route('portal'));

        $this->assertDatabaseHas(Presenca::class, [
            'aula_id' => $aula->id,
            'user_id' => $colaborador->id,
            'metodo' => 'facial',
        ]);
        $this->assertDatabaseHas(Auditoria::class, ['evento' => 'presenca_facial']);
    }

    public function test_fora_da_janela_nao_registra_facial(): void
    {
        foreach ([StatusAula::Agendada, StatusAula::Encerrada] as $status) {
            $aula = $this->criarAula($status, $this->criarInstrutor());
            $colaborador = $this->criarColaborador();
            $this->aceitarTermo($colaborador);

            $this->actingAs($colaborador)
                ->post(route('portal.presenca.facial', ['aula_id' => $aula->id]))
                ->assertSessionHasErrors('aula'); // RF03: só com instrutor iniciou e não encerrou

            $this->assertDatabaseMissing(Presenca::class, ['user_id' => $colaborador->id]);
        }
    }

    public function test_sem_aceite_de_termo_vai_para_tela_do_termo(): void
    {
        $aula = $this->criarAula(StatusAula::EmAndamento, $this->criarInstrutor());
        $colaborador = $this->criarColaborador(); // sem aceite (RF04)

        $this->actingAs($colaborador)
            ->post(route('portal.presenca.facial', ['aula_id' => $aula->id]))
            ->assertRedirect(route('termo.mostrar'));

        $this->assertDatabaseMissing(Presenca::class, ['aula_id' => $aula->id]);
    }

    public function test_duplicidade_e_recusada_e_fica_auditoria(): void
    {
        $aula = $this->criarAula(StatusAula::EmAndamento, $this->criarInstrutor());
        $colaborador = $this->criarColaborador();
        $this->aceitarTermo($colaborador);
        Presenca::create(['aula_id' => $aula->id, 'user_id' => $colaborador->id, 'metodo' => 'facial']);

        $this->actingAs($colaborador)
            ->post(route('portal.presenca.facial', ['aula_id' => $aula->id]))
            ->assertSessionHasErrors('duplicidade'); // RF16: 1 presença por pessoa por aula

        $this->assertDatabaseHas(Auditoria::class, ['evento' => 'presenca_duplicada']);
        $this->assertEquals(1, Presenca::where('aula_id', $aula->id)->count());
    }

    public function test_instrutor_aponta_facial_para_o_colaborador(): void
    {
        $instrutor = $this->criarInstrutor();
        $aula = $this->criarAula(StatusAula::EmAndamento, $instrutor);
        $this->aceitarTermo($instrutor);
        $colaborador = $this->criarColaborador();
        $this->aceitarTermo($colaborador);

        $this->actingAs($instrutor)
            ->post(route('portal.aula.presenca.facial', ['aula' => $aula]), [
                'colaborador_id' => $colaborador->id,
            ])
            ->assertRedirect(route('portal.aula.mostrar', $aula)); // RF14: via instrutor

        $this->assertDatabaseHas(Presenca::class, [
            'aula_id' => $aula->id,
            'user_id' => $colaborador->id,
            'metodo' => 'facial',
        ]);
    }

    public function test_instrutor_de_outra_aula_nao_aponta_presenca(): void
    {
        $aula = $this->criarAula(StatusAula::EmAndamento, $this->criarInstrutor());
        $outroInstrutor = $this->criarInstrutor();
        $this->aceitarTermo($outroInstrutor);
        $colaborador = $this->criarColaborador();
        $this->aceitarTermo($colaborador);

        $this->actingAs($outroInstrutor)
            ->post(route('portal.aula.presenca.facial', ['aula' => $aula]), [
                'colaborador_id' => $colaborador->id,
            ])
            ->assertForbidden(); // RF06: instrutor conduz apenas SUAS aulas
    }
}
