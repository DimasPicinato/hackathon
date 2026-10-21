<?php

namespace Tests\Feature;

// RF09, RF10–RF12 — resolução de tickets com efeito real (vaga + fila + justificativa)
use App\Models\Inscricao;
use App\Models\Justificativa;
use App\Models\Notificacao;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CriaFixtures;
use Tests\TestCase;

class ResolucaoTicketsTest extends TestCase
{
    use RefreshDatabase;
    use CriaFixtures;

    public function test_cancelar_vaga_promove_a_fila_automat_icamente(): void
    {
        $setor = $this->criarSetor();
        $titular = $this->criarColaborador(['setor_id' => $setor->id]);
        $daFila = $this->criarColaborador(['setor_id' => $setor->id]);
        $rh = $this->criarRh();

        $aula = $this->criarAula(
            status: \App\Enums\StatusAula::Agendada,
            atributos: ['vagas' => 1],
        );
        $this->inscreverComStatus($aula, $titular, 'reservada');
        $inscricaoFila = $this->inscreverComStatus($aula, $daFila, 'lista_espera', 1);

        $ticket = $this->criarTicket($titular, $aula->treinamento, 'Cancelar vaga');
        $this->aceitarTermo($rh);

        $this->actingAs($rh)
            ->post(route('portal.rh.tickets.resolver', $ticket), ['acao' => 'cancelar_vaga'])
            ->assertRedirect();

        $ticket->refresh();
        $this->assertSame('resolvido', $ticket->status->value); // RF12
        $this->assertSame('cancelada',
            Inscricao::where('aula_id', $aula->id)->where('user_id', $titular->id)->value('status'));

        $promovida = Inscricao::where('aula_id', $aula->id)->where('user_id', $daFila->id)->first();
        $this->assertSame('reservada', $promovida->status);          // RF09: vaga ocupada
        $this->assertNull($promovida->posicao_fila);

        $this->assertDatabaseHas(Notificacao::class, [              // promoted person learns
            'user_id' => $daFila->id,
            'tipo' => 'fila_promocao',
        ]);
        $this->assertDatabaseHas(Notificacao::class, [              // ticket author learns
            'user_id' => $titular->id,
            'tipo' => 'ticket_resolvido',
        ]);
    }

    public function test_justificar_falta_via_ticket_cria_justificativa(): void
    {
        $rh = $this->criarRh();
        $this->aceitarTermo($rh);
        $aula = $this->criarAula(\App\Enums\StatusAula::Encerrada, $this->criarInstrutor());
        $faltoso = $this->criarColaborador();
        $ticket = $this->criarTicket($faltoso, $aula->treinamento, 'Justificar falta', 'Gripe com atestado.');

        $this->actingAs($rh)
            ->post(route('portal.rh.tickets.resolver', $ticket), [
                'acao' => 'justificar_falta',
                'aula_id' => $aula->id,
                'status_justificativa' => 'aprovado',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas(Justificativa::class, [
            'user_id' => $faltoso->id,
            'aula_id' => $aula->id,
            'status' => 'aprovado',
            'ticket_id' => $ticket->id,
        ]);
        $this->assertDatabaseHas(Notificacao::class, ['user_id' => $faltoso->id, 'tipo' => 'justificativa_alterada']);
    }

    public function test_justificar_falta_sem_informar_a_aula_e_recusado(): void
    {
        $rh = $this->criarRh();
        $this->aceitarTermo($rh);
        $faltoso = $this->criarColaborador();
        $ticket = $this->criarTicket($faltoso, $this->criarTreinamento(), 'Justificar falta');

        $this->actingAs($rh)
            ->post(route('portal.rh.tickets.resolver', $ticket), ['acao' => 'justificar_falta'])
            ->assertStatus(422); // sem aula escolhida não há como registrar a falta

        $this->assertDatabaseMissing(Justificativa::class, ['ticket_id' => $ticket->id]);
    }

    public function test_lider_resolve_apenas_tickets_do_seu_setor(): void
    {
        $setorMeu = $this->criarSetor('Manutenção');
        $setorOutro = $this->criarSetor('Laboratório');
        $lider = User::factory()->comPapeis('lider')->create(['setor_id' => $setorMeu->id]);
        $this->aceitarTermo($lider);

        $dentro = $this->criarTicket(
            $this->criarColaborador(['setor_id' => $setorMeu->id]),
            $this->criarTreinamento(),
            'Informação',
        );
        $fora = $this->criarTicket(
            $this->criarColaborador(['setor_id' => $setorOutro->id]),
            $this->criarTreinamento(),
            'Informação',
        );

        $this->actingAs($lider)
            ->post(route('portal.rh.tickets.resolver', $fora), ['acao' => 'encerrar'])
            ->assertForbidden(); // RF12: líder só resolve dos liderados

        $this->actingAs($lider)
            ->post(route('portal.rh.tickets.resolver', $dentro), ['acao' => 'encerrar', 'resultado' => 'Respondido: sim.'])
            ->assertRedirect();

        $this->assertSame('resolvido', $dentro->fresh()->status->value);
    }

    public function test_colaborador_nao_entraria_na_area_de_gestao(): void
    {
        $colaborador = $this->criarColaborador();
        $this->aceitarTermo($colaborador);

        $this->actingAs($colaborador)
            ->get(route('portal.rh.tickets'))
            ->assertForbidden();
    }
}
