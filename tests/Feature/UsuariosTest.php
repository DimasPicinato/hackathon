<?php

namespace Tests\Feature;

// RF05 — CRUD sem self-service: RH cria/ajusta/desativa; líder só senha de liderados; desativado não loga
use App\Models\Auditoria;
use App\Models\Notificacao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\CriaFixtures;
use Tests\TestCase;

class UsuariosTest extends TestCase
{
    use RefreshDatabase;
    use CriaFixtures;

    public function test_rh_cria_usuario_com_papeis_acumulaveis(): void
    {
        $setor = $this->criarSetor('Injeção');
        $rh = $this->criarRh();
        $this->aceitarTermo($rh);

        $this->actingAs($rh)
            ->post(route('portal.rh.usuarios.criar'), [
                'name' => 'Nova Pessoa Exemplo',
                'email' => 'nova.pessoa@exemplo.com',
                'matricula' => 'U-77777',
                'setor_id' => $setor->id,
                'turno' => 'tarde',
                'papeis' => ['colaborador', 'instrutor'],
                'password' => 'SenhaForte@2026',
            ])
            ->assertRedirect()
            ->assertSessionHas('ok');

        $usuario = User::where('matricula', 'U-77777')->first();
        $this->assertNotNull($usuario);
        $this->assertTrue(Hash::check('SenhaForte@2026', $usuario->password));
        $this->assertContains('instrutor', $usuario->papeis);
        $this->assertDatabaseHas(Auditoria::class, ['evento' => 'usuario_criado']);
        $this->assertDatabaseHas(Notificacao::class, ['user_id' => $usuario->id, 'tipo' => 'conta']); // RF24
    }

    public function test_usuario_desativado_nao_loga(): void
    {
        $usuario = $this->criarColaborador();
        $usuario->update(['ativo' => false]);

        $this->post('/login', [
            'email' => $usuario->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email'); // RF05: conta desativada pelo RH

        $this->assertGuest();
    }

    public function test_rh_desativa_e_reativa(): void
    {
        $rh = $this->criarRh();
        $this->aceitarTermo($rh);
        $usuario = $this->criarColaborador();

        $this->actingAs($rh)
            ->post(route('portal.rh.usuarios.alternar', $usuario))
            ->assertRedirect()
            ->assertSessionHas('ok');

        $this->assertFalse($usuario->fresh()->ativo);
        $this->assertDatabaseHas(Auditoria::class, ['evento' => 'usuario_desativado']);

        $this->actingAs($rh)
            ->post(route('portal.rh.usuarios.alternar', $usuario))
            ->assertRedirect();

        $this->assertTrue($usuario->fresh()->ativo);
        $this->assertDatabaseHas(Auditoria::class, ['evento' => 'usuario_reativado']);
    }

    public function test_lider_redefine_senha_somente_de_liderados(): void
    {
        $setor = $this->criarSetor('WTC');
        $setorOutro = $this->criarSetor('Compras');
        $lider = User::factory()->comPapeis('lider')->create(['setor_id' => $setor->id]);
        $this->aceitarTermo($lider);
        $liderado = $this->criarColaborador(['setor_id' => $setor->id]);
        $estranho = $this->criarColaborador(['setor_id' => $setorOutro->id]);
        $senhaAntiga = $liderado->password;

        $this->actingAs($lider)
            ->post(route('portal.rh.usuarios.senha', $estranho), ['senha' => 'NovaTemp@2026'])
            ->assertForbidden(); // RF05: fora do escopo do líder

        $this->actingAs($lider)
            ->post(route('portal.rh.usuarios.senha', $liderado), ['senha' => 'NovaTemp@2026'])
            ->assertRedirect()
            ->assertSessionHas('ok');

        $this->assertTrue(Hash::check('NovaTemp@2026', $liderado->fresh()->password));
        $this->assertNotEquals($senhaAntiga, $liderado->fresh()->password);
        $this->assertDatabaseHas(Auditoria::class, ['evento' => 'senha_redefinida']);
    }

    public function test_colaborador_nao_gerencia_usuarios(): void
    {
        $colaborador = $this->criarColaborador();
        $this->aceitarTermo($colaborador);

        $this->actingAs($colaborador)
            ->get(route('portal.rh.usuarios'))
            ->assertForbidden();
    }
}
