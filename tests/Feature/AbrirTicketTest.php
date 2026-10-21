<?php

namespace Tests\Feature;

// RF10, RF11, RF24 — colaborador abre ticket do catálogo; só submete; RH é notificado
use App\Models\Auditoria;
use App\Models\Notificacao;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CriaFixtures;
use Tests\TestCase;

class AbrirTicketTest extends TestCase
{
    use RefreshDatabase;
    use CriaFixtures;

    public function test_colaborador_abre_ticket_via_parametros_e_rh_recebe_a_notificacao(): void
    {
        $colaborador = $this->criarColaborador();
        $this->aceitarTermo($colaborador);
        \App\Models\TipoTicket::create(['nome' => fake()->unique()->word(), 'ativo' => true]);
        \App\Models\TipoTicket::create(['nome' => fake()->unique()->word(), 'ativo' => false]); // inativo não deve ser aceito
        $aberto = \App\Models\TipoTicket::create(['nome' => fake()->unique()->word(), 'ativo' => true]);
        $treinamento = $this->criarTreinamento();
        $rh = $this->criarRh();
        $rh2 = $this->criarRh();

        $this->actingAs($colaborador)
            ->post(route('portal.ticket.abrir'), [
                'tipo_ticket_id' => $aberto->id,
                'treinamento_id' => $treinamento->id,
                'texto' => 'Vou faltar na aula 2 por atestado médico.',
            ])
            ->assertRedirect()
            ->assertSessionHas('ok');

        $this->assertDatabaseHas(Ticket::class, [
            'user_id' => $colaborador->id,
            'tipo_ticket_id' => $aberto->id,
            'treinamento_id' => $treinamento->id,
            'status' => 'aberto',
        ]);
        $this->assertDatabaseHas(Auditoria::class, ['evento' => 'ticket_aberto']);

        $this->assertSame(2, Notificacao::where('tipo', 'ticket_novo')->count()); // RF24: todo o RH
        $this->assertDatabaseHas(Notificacao::class, ['user_id' => $rh->id]);
        $this->assertDatabaseHas(Notificacao::class, ['user_id' => $rh2->id]);
    }

    public function test_tipo_inativo_no_catalogo_e_recusado(): void
    {
        $colaborador = $this->criarColaborador();
        $this->aceitarTermo($colaborador);
        $morto = \App\Models\TipoTicket::create(['nome' => fake()->unique()->word(), 'ativo' => false]); // RF26 pode desativar

        $this->actingAs($colaborador)
            ->post(route('portal.ticket.abrir'), [
                'tipo_ticket_id' => $morto->id,
                'texto' => 'Tentativa com tipo desativado.',
            ])
            ->assertSessionHasErrors('tipo_ticket_id');

        $this->assertDatabaseMissing(Ticket::class, ['user_id' => $colaborador->id]);
    }

    public function test_ticket_sem_texto_livre_e_recusado(): void
    {
        $colaborador = $this->criarColaborador();
        $this->aceitarTermo($colaborador);
        $tipo = \App\Models\TipoTicket::create(['nome' => fake()->unique()->word(), 'ativo' => true]);

        $this->actingAs($colaborador)
            ->post(route('portal.ticket.abrir'), ['tipo_ticket_id' => $tipo->id])
            ->assertSessionHasErrors('texto'); // RF11: texto livre obrigatório

        $this->assertDatabaseMissing(Ticket::class, ['user_id' => $colaborador->id]);
    }
}
