<?php

namespace Tests\Feature;

// RF15, RF27 — homologação manual: senha do próprio colaborador + motivo
use App\Enums\StatusAula;
use App\Models\Auditoria;
use App\Models\Aula;
use App\Models\MotivoHomologacao;
use App\Models\Notificacao;
use App\Models\Presenca;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CriaFixtures;
use Tests\TestCase;

class HomologacaoManualTest extends TestCase
{
    use RefreshDatabase;
    use CriaFixtures;

    public function test_homologacao_com_senha_e_motivo_do_catalogo(): void
    {
        $instrutor = $this->criarInstrutor();
        $aula = $this->criarAula(StatusAula::Encerrada, $instrutor); // fora da janela: manual permite (RF03)
        $this->aceitarTermo($instrutor);
        $colaborador = $this->criarColaborador(['password' => 'SenhaDemo@123']);
        $motivo = MotivoHomologacao::create(['nome' => 'Câmera desligada ou indisponível', 'ativo' => true]);
        $this->criarRh(); // destinatário da notificação

        $this->actingAs($instrutor)
            ->post(route('portal.aula.presenca.manual', ['aula' => $aula]), [
                'colaborador_id' => $colaborador->id,
                'senha' => 'SenhaDemo@123', // anti-fraude RF15: senha do PRÓPRIO colaborador
                'motivo_id' => $motivo->id,
            ])
            ->assertRedirect(route('portal.aula.mostrar', $aula));

        $presenca = Presenca::query()
            ->where('aula_id', $aula->id)->where('user_id', $colaborador->id)->first();

        $this->assertNotNull($presenca);
        $this->assertSame('manual', $presenca->metodo->value);
        $this->assertTrue($presenca->senha_colaborador_validada);
        $this->assertSame($motivo->id, $presenca->motivo_id);
        $this->assertDatabaseHas(Auditoria::class, ['evento' => 'presenca_homologada']);
        $this->assertDatabaseHas(Notificacao::class, [ // RF24: presença homologada notifica RH
            'tipo' => 'presenca_homologada',
        ]);
    }

    public function test_senha_errada_e_recusada_com_auditoria(): void
    {
        $instrutor = $this->criarInstrutor();
        $aula = $this->criarAula(StatusAula::EmAndamento, $instrutor);
        $this->aceitarTermo($instrutor);
        $colaborador = $this->criarColaborador(['password' => 'SenhaDemo@123']);
        $motivo = MotivoHomologacao::create(['nome' => 'Falha no reconhecimento facial', 'ativo' => true]);

        $this->actingAs($instrutor)
            ->post(route('portal.aula.presenca.manual', ['aula' => $aula]), [
                'colaborador_id' => $colaborador->id,
                'senha' => 'SenhaERRADA@123',
                'motivo_id' => $motivo->id,
            ])
            ->assertSessionHasErrors('senha'); // RF15: anti-fraude

        $this->assertDatabaseMissing(Presenca::class, ['aula_id' => $aula->id, 'user_id' => $colaborador->id]);
        $this->assertDatabaseHas(Auditoria::class, ['evento' => 'homologacao_senha_incorreta']);
    }

    public function test_sem_motivo_e_recusado(): void
    {
        $instrutor = $this->criarInstrutor();
        $aula = $this->criarAula(StatusAula::EmAndamento, $instrutor);
        $this->aceitarTermo($instrutor);
        $colaborador = $this->criarColaborador();

        $this->actingAs($instrutor)
            ->post(route('portal.aula.presenca.manual', ['aula' => $aula]), [
                'colaborador_id' => $colaborador->id,
                'senha' => $colaborador->password,
            ])
            ->assertSessionHasErrors(); // motivo é obrigatório (RF15)
    }

    public function test_motivo_novo_no_ato_alimenta_o_catalogo(): void
    {
        $instrutor = $this->criarInstrutor();
        $aula = $this->criarAula(StatusAula::EmAndamento, $instrutor);
        $this->aceitarTermo($instrutor);
        $colaborador = $this->criarColaborador(['password' => 'SenhaDemo@123']);

        $this->actingAs($instrutor)
            ->post(route('portal.aula.presenca.manual', ['aula' => $aula]), [
                'colaborador_id' => $colaborador->id,
                'senha' => 'SenhaDemo@123',
                'motivo_novo' => 'Câmera do galpão com defeito hoje',
            ])
            ->assertRedirect(route('portal.aula.mostrar', $aula));

        $this->assertDatabaseHas(MotivoHomologacao::class, [
            'nome' => 'Câmera do galpão com defeito hoje',
            'criado_por_id' => $instrutor->id,
            'ativo' => true,
        ]); // RF27: instrutor cadastra no ato; lista passa a integrar o catálogo

        $presenca = Presenca::query()->where('aula_id', $aula->id)->first();
        $this->assertSame('manual', $presenca->metodo->value);
    }

    public function test_duplicidade_manual_tambem_e_recusada(): void
    {
        $instrutor = $this->criarInstrutor();
        $aula = $this->criarAula(StatusAula::EmAndamento, $instrutor);
        $this->aceitarTermo($instrutor);
        $colaborador = $this->criarColaborador(['password' => 'SenhaDemo@123']);
        Presenca::create(['aula_id' => $aula->id, 'user_id' => $colaborador->id, 'metodo' => 'facial']);

        $this->actingAs($instrutor)
            ->post(route('portal.aula.presenca.manual', ['aula' => $aula]), [
                'colaborador_id' => $colaborador->id,
                'senha' => 'SenhaDemo@123',
                'motivo_novo' => 'Outro motivo',
            ])
            ->assertSessionHasErrors('duplicidade'); // RF16 vale para facial E manual

        $this->assertEquals(1, Presenca::where('aula_id', $aula->id)->count());
    }
}
