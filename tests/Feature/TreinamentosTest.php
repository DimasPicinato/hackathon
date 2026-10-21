<?php

namespace Tests\Feature;

// RF07 — RH/Admin criam/editam; Líder só no próprio setor; aula fechada não é re-agendada aqui
use App\Models\Aula;
use App\Models\Auditoria;
use App\Models\Treinamento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CriaFixtures;
use Tests\TestCase;

class TreinamentosTest extends TestCase
{
    use RefreshDatabase;
    use CriaFixtures;

    public function test_rh_cria_treinamento_e_aula_em_sequencia(): void
    {
        $rh = $this->criarRh();
        $this->aceitarTermo($rh);

        $this->actingAs($rh)
            ->post(route('portal.rh.treinamentos.criar'), [
                'nome' => fake()->unique()->company() . ' — NR12 Máquinas',
                'carga_horaria' => 16,
                'validade_meses' => 24,
            ])
            ->assertRedirect()
            ->assertSessionHas('ok');

        $treinamento = Treinamento::where('carga_horaria', 16)->first();
        $this->assertNotNull($treinamento);
        $this->assertDatabaseHas(Auditoria::class, ['evento' => 'treinamento_criado']);

        $this->actingAs($rh)
            ->post(route('portal.rh.treinamentos.aula.criar', $treinamento), [
                'titulo' => 'Aula 1 — proteções fixas',
                'data_inicio' => now()->addDays(10)->format('Y-m-d H:i'),
                'vagas' => 20,
                'local' => 'Galpão C',
            ])
            ->assertRedirect();

        $aula = Aula::where('treinamento_id', $treinamento->id)->first();
        $this->assertSame(1, $aula->ordem);            // ordem calculada
        $this->assertSame('agendada', $aula->status->value);
        $this->assertDatabaseHas(Auditoria::class, ['evento' => 'aula_criada']);
    }

    public function test_lider_cria_somente_para_o_proprio_setor(): void
    {
        $setorMeu = $this->criarSetor('Montagem');
        $setorOutro = $this->criarSetor('PCP');
        $lider = \App\Models\User::factory()->comPapeis('lider')->create(['setor_id' => $setorMeu->id]);
        $this->aceitarTermo($lider);

        $this->actingAs($lider)
            ->post(route('portal.rh.treinamentos.criar'), [
                'nome' => fake()->unique()->company() . ' — Estoque Seguro',
                'carga_horaria' => 4,
                'setor_id' => $setorOutro->id,         // tentou outro setor...
            ])
            ->assertRedirect()
            ->assertSessionHas('ok');

        $this->assertSame($setorMeu->id,                // ...mas o próprio setor é imposto (RF07)
            Treinamento::where('carga_horaria', 4)->value('setor_id'));
    }

    public function test_lider_nao_edita_treinamento_de_outro_setor(): void
    {
        $setorOutro = $this->criarSetor('Energia');
        $lider = \App\Models\User::factory()->comPapeis('lider')->create(); // sem setor — fora de tudo
        $this->aceitarTermo($lider);
        $treinamento = Treinamento::create([
            'nome' => fake()->unique()->company() . ' — Eletricista',
            'carga_horaria' => 8,
            'setor_id' => $setorOutro->id,
        ]);

        $this->actingAs($lider)
            ->patch(route('portal.rh.treinamentos.atualizar', $treinamento), [
                'nome' => $treinamento->nome,
                'carga_horaria' => 99,
            ])
            ->assertForbidden(); // RF07: fora do escopo
    }

    public function test_aula_fechada_nao_e_re_agendada_pela_gestao(): void
    {
        $rh = $this->criarRh();
        $this->aceitarTermo($rh);
        $aula = $this->criarAula(\App\Enums\StatusAula::EmAndamento, $this->criarInstrutor());

        $codigo = $this->actingAs($rh)
            ->patch(route('portal.rh.treinamentos.aula.atualizar', $aula), [
                'titulo' => $aula->titulo,
                'data_inicio' => now()->addDays(5)->format('Y-m-d\TH:i'),
                'vagas' => 30,
            ])
            ->assertStatus(422); // janela é do instrutor (RF06)

        $this->assertSame($aula->vagas, $aula->fresh()->vagas);
    }

    public function test_rh_ajusta_aula_agendada(): void
    {
        $rh = $this->criarRh();
        $this->aceitarTermo($rh);
        $aula = $this->criarAula(); // agendada

        $this->actingAs($rh)
            ->patch(route('portal.rh.treinamentos.aula.atualizar', $aula), [
                'titulo' => $aula->titulo,
                'data_inicio' => $aula->data_inicio->copy()->addDay()->format('Y-m-d\TH:i'),
                'vagas' => 42,
            ])
            ->assertRedirect()
            ->assertSessionHas('ok');

        $this->assertSame(42, $aula->fresh()->vagas);
        $this->assertDatabaseHas(Auditoria::class, ['evento' => 'aula_ajustada']);
    }
}
