<?php

namespace Tests\Feature;

// RF04 — aceite do termo LGPD de biometria no primeiro login / nova versão
use App\Models\TermoAceite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CriaFixtures;
use Tests\TestCase;

class AceiteTermoTest extends TestCase
{
    use RefreshDatabase;
    use CriaFixtures;

    public function test_portal_e_bloqueado_ate_o_aceite(): void
    {
        $user = $this->criarColaborador(); // termo existe? Não — primeiro criamos o termo
        $this->criarTermo();

        $this->actingAs($user)
            ->get(route('portal'))
            ->assertRedirect(route('termo.mostrar')); // RF04: porta de entrada do portal
    }

    public function test_aceite_libera_portal_e_deixa_rastro(): void
    {
        $user = $this->criarColaborador();
        $termo = $this->criarTermo();

        $this->actingAs($user)
            ->post(route('termo.aceitar'), ['concordo' => '1'])
            ->assertRedirect(route('portal'));

        $this->assertDatabaseHas(TermoAceite::class, [
            'user_id' => $user->id,
            'termo_id' => $termo->id,
        ]);

        $this->actingAs($user)->get(route('portal'))->assertOk();
        $this->assertTrue($user->fresh()->aceitouTermoVigente());
    }

    public function test_aceite_sem_marcacao_e_recusado(): void
    {
        $user = $this->criarColaborador();
        $this->criarTermo();

        $this->actingAs($user)
            ->from(route('termo.mostrar'))
            ->post(route('termo.aceitar'))
            ->assertSessionHasErrors('concordo');

        $this->assertDatabaseMissing(TermoAceite::class, ['user_id' => $user->id]);
    }

    public function test_nova_versao_do_termo_repede_aceite(): void
    {
        $user = $this->criarColaborador();
        $v1 = $this->aceitarTermo($user);
        $v1->update(['ativo' => false]);
        $this->criarTermo(); // v2 vigente

        $this->actingAs($user)
            ->get(route('portal'))
            ->assertRedirect(route('termo.mostrar')); // RF04: nova versão re-pede na entrada seguinte
    }

    public function test_login_devolve_para_o_portal(): void
    {
        $user = User::factory()->create(); // senha padrão 'password'

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('portal', absolute: false));

        $this->assertAuthenticated();
    }
}
