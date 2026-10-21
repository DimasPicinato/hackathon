<?php

namespace Tests\Feature;

// RF26 (e RF27/RF04 na ponta) — painel de configurações de Admin/RH
use App\Models\Auditoria;
use App\Models\MotivoHomologacao;
use App\Models\Termo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CriaFixtures;
use Tests\TestCase;

class ConfiguracoesRhTest extends TestCase
{
    use RefreshDatabase;
    use CriaFixtures;

    public function test_rh_cria_e_alterna_motivo_do_catalogo(): void
    {
        $rh = $this->criarRh();
        $this->aceitarTermo($rh);

        $this->actingAs($rh)
            ->post(route('portal.rh.configuracoes.motivo.criar'), ['nome' => 'Troca de turno de última hora'])
            ->assertRedirect();

        $motivo = MotivoHomologacao::where('nome', 'Troca de turno de última hora')->first();
        $this->assertNotNull($motivo->ativo);
        $this->assertDatabaseHas(Auditoria::class, ['evento' => 'motivo_criado']);

        $this->actingAs($rh)
            ->post(route('portal.rh.configuracoes.motivo.alternar', $motivo))
            ->assertRedirect();

        $this->assertFalse($motivo->fresh()->ativo);
        $this->assertDatabaseHas(Auditoria::class, ['evento' => 'motivo_alternado']);
    }

    public function test_publicar_novo_termo_repede_aceite_de_todos(): void
    {
        $rh = $this->criarRh();
        $usuario = $this->criarColaborador();
        $this->aceitarTermo($usuario);           // aceite na v1
        $v1 = Termo::query()->where('ativo', true)->first();
        $this->aceitarTermo($rh);

        $this->actingAs($rh)
            ->post(route('portal.rh.configuracoes.termo.publicar'), [
                'versao' => 'v2.0',
                'titulo' => 'Consentimento LGPD ampliado',
                'conteudo' => 'Novas finalidades explicadas…',
            ])
            ->assertRedirect();

        $v1->refresh();
        $this->assertFalse($v1->ativo);
        $this->assertSame('v2.0', Termo::where('ativo', true)->value('versao'));

        // quem já tinha aceite na v1 é re-imposto a aceitar no próximo portal (RF04)
        $this->actingAs($usuario)
            ->get(route('portal'))
            ->assertRedirect(route('termo.mostrar'));

        $this->assertDatabaseHas(Auditoria::class, ['evento' => 'termo_publicado']);
    }

    public function test_colaborador_nao_gerencia_configuracoes(): void
    {
        $colaborador = $this->criarColaborador();
        $this->aceitarTermo($colaborador);

        $this->actingAs($colaborador)
            ->get(route('portal.rh.configuracoes'))
            ->assertForbidden(); // RF26: Admin/RH apenas
    }

    public function test_alternar_tipo_ticket(): void
    {
        $rh = $this->criarRh();
        $this->aceitarTermo($rh);
        $tipo = \App\Models\TipoTicket::create(['nome' => fake()->unique()->word(), 'ativo' => true]);

        $this->actingAs($rh)
            ->post(route('portal.rh.configuracoes.tipo.alternar', $tipo))
            ->assertRedirect();

        $this->assertFalse($tipo->fresh()->ativo);
    }
}
