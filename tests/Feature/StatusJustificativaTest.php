<?php

namespace Tests\Feature;

// RF12, RF13, RF24 — justificativa: status a qualquer momento; líder registra direto
use App\Models\Auditoria;
use App\Models\Notificacao;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CriaFixtures;
use Tests\TestCase;

class StatusJustificativaTest extends TestCase
{
    use RefreshDatabase;
    use CriaFixtures;

    public function test_rh_altera_status_a_qualquer_momento(): void
    {
        $rh = $this->criarRh();
        $this->aceitarTermo($rh);
        $aula = $this->criarAula(\App\Enums\StatusAula::Encerrada, $this->criarInstrutor());
        $just = $this->criarJustificativa($aula, $this->criarColaborador(), 'pendente');

        $this->actingAs($rh)
            ->post(route('portal.rh.justificativas.status', $just), ['status' => 'recusado'])
            ->assertRedirect();

        $just->refresh();
        $this->assertSame('recusado', $just->status->value);
        $this->assertSame($rh->id, $just->alterado_por_id);
        $this->assertNotNull($just->alterado_em);

        $this->assertDatabaseHas(Auditoria::class, ['evento' => 'justificativa_alterada']);
        $this->assertDatabaseHas(Notificacao::class, [ // RF24: colaborador notificado
            'user_id' => $just->user_id,
            'tipo' => 'justificativa_alterada',
        ]);
    }

    public function test_lider_do_setor_altera_e_fora_do_setor_e_403(): void
    {
        $setorMeu = $this->criarSetor('Expedição');
        $setorOutro = $this->criarSetor('Qualidade');
        $lider = \App\Models\User::factory()->comPapeis('lider')->create(['setor_id' => $setorMeu->id]);
        $this->aceitarTermo($lider);

        $dentro = $this->criarJustificativa(
            $this->criarAula(\App\Enums\StatusAula::Encerrada, $this->criarInstrutor()),
            $this->criarColaborador(['setor_id' => $setorMeu->id]),
        );
        $fora = $this->criarJustificativa(
            $this->criarAula(\App\Enums\StatusAula::Encerrada, $this->criarInstrutor()),
            $this->criarColaborador(['setor_id' => $setorOutro->id]),
        );

        $this->actingAs($lider)
            ->post(route('portal.rh.justificativas.status', $fora), ['status' => 'aprovado'])
            ->assertForbidden(); // RF12: líder só alcança liderados

        $this->actingAs($lider)
            ->post(route('portal.rh.justificativas.status', $dentro), ['status' => 'aprovado'])
            ->assertRedirect();
        $this->assertSame('aprovado', $dentro->fresh()->status->value);
    }

    public function test_registro_direto_pelo_lider_sem_ticket_rf13(): void
    {
        $setor = $this->criarSetor('Empilhadeiras');
        $lider = \App\Models\User::factory()->comPapeis('lider')->create(['setor_id' => $setor->id]);
        $this->aceitarTermo($lider);
        $liderado = $this->criarColaborador(['setor_id' => $setor->id]);
        $aula = $this->criarAula(\App\Enums\StatusAula::Encerrada, $this->criarInstrutor());

        $this->actingAs($lider)
            ->post(route('portal.rh.justificativas.registrar'), [
                'user_id' => $liderado->id,
                'aula_id' => $aula->id,
                'status' => 'aprovado',
                'texto' => 'Faltou mas avisou antes, coberto por banco de horas.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('justificativas', [
            'user_id' => $liderado->id,
            'aula_id' => $aula->id,
            'status' => 'aprovado',
            'ticket_id' => null,
        ]);
        $this->assertDatabaseHas(Auditoria::class, ['evento' => 'justificativa_registrada']);
    }

    public function test_registro_direto_fora_do_escopo_e_403(): void
    {
        $setorMeu = $this->criarSetor('Recebimento');
        $setorOutro = $this->criarSetor('Expedição');
        $lider = \App\Models\User::factory()->comPapeis('lider')->create(['setor_id' => $setorMeu->id]);
        $this->aceitarTermo($lider);
        $aula = $this->criarAula(\App\Enums\StatusAula::Encerrada, $this->criarInstrutor());

        $this->actingAs($lider)
            ->post(route('portal.rh.justificativas.registrar'), [
                'user_id' => $this->criarColaborador(['setor_id' => $setorOutro->id])->id,
                'aula_id' => $aula->id,
                'status' => 'aprovado',
            ])
            ->assertForbidden(); // fora dos liderados: negó
    }
}
