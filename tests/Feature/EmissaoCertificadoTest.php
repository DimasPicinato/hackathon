<?php

namespace Tests\Feature;

// RF18–RF20 — emissão manual pelo RH, elegibilidade e imutabilidade na reemissão
use App\Models\Auditoria;
use App\Models\Certificado;
use App\Models\Notificacao;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CriaFixtures;
use Tests\TestCase;

class EmissaoCertificadoTest extends TestCase
{
    use RefreshDatabase;
    use CriaFixtures;

    public function test_rh_emite_para_quem_compareceu_a_todas_as_aulas(): void
    {
        $instrutor = $this->criarInstrutor();
        $aula1 = $this->criarAula(\App\Enums\StatusAula::Encerrada, $instrutor);
        $aula2 = $this->criarAula(\App\Enums\StatusAula::Encerrada, $instrutor, [
            'treinamento_id' => $aula1->treinamento_id,
            'ordem' => 2,
        ]);
        $treinamento = $aula1->treinamento;
        $aula2->update(['data_inicio' => $aula1->data_inicio->copy()->addDays(7)]); // última aula

        $colaborador = $this->criarColaborador();
        $this->darPresenca($aula1, $colaborador);
        $this->darPresenca($aula2, $colaborador);

        $rh = $this->criarRh();
        $this->aceitarTermo($rh);

        $this->actingAs($rh)
            ->post(route('portal.rh.certificados.emitir'), [
                'user_id' => $colaborador->id,
                'treinamento_id' => $treinamento->id,
            ])
            ->assertRedirect();

        $cert = Certificado::where('user_id', $colaborador->id)->first();
        $this->assertNotNull($cert);
        $this->assertSame(1, $cert->versao);
        $this->assertStringStartsWith('PZ-', $cert->codigo);
        $this->assertSame(
            $aula2->data_inicio->copy()->addMonths($treinamento->validade_meses)->format('Y-m-d'),
            $cert->valido_ate->format('Y-m-d'), // RF22: validade pela última aula
        );

        $this->assertDatabaseHas(Auditoria::class, ['evento' => 'certificado_emitido']);
        $this->assertDatabaseHas(Notificacao::class, [ // RF24: emitido notifica o colaborador
            'user_id' => $colaborador->id,
            'tipo' => 'certificado_emitido',
        ]);
    }

    public function test_falta_em_uma_aula_bloqueia_emissao(): void
    {
        $aula1 = $this->criarAula(\App\Enums\StatusAula::Encerrada, $this->criarInstrutor());
        $aula2 = $this->criarAula(\App\Enums\StatusAula::Encerrada, $this->criarInstrutor(), [
            'treinamento_id' => $aula1->treinamento_id,
            'ordem' => 2,
        ]);
        $colaborador = $this->criarColaborador();
        $this->darPresenca($aula1, $colaborador); // faltou na aula 2 — RF18
        $rh = $this->criarRh();
        $this->aceitarTermo($rh);

        $this->actingAs($rh)
            ->post(route('portal.rh.certificados.emitir'), [
                'user_id' => $colaborador->id,
                'treinamento_id' => $aula1->treinamento_id,
            ])
            ->assertSessionHasErrors('elegibilidade');

        $this->assertDatabaseMissing(Certificado::class, ['user_id' => $colaborador->id]);
    }

    public function test_apenas_rh_emite(): void
    {
        $colaborador = $this->criarColaborador();
        $this->aceitarTermo($colaborador);

        $this->actingAs($colaborador)
            ->post(route('portal.rh.certificados.emitir'), [
                'user_id' => $colaborador->id,
                'treinamento_id' => $this->criarTreinamento()->id,
            ])
            ->assertForbidden(); // RF19: emissão exclusiva do RH
    }

    public function test_reemissao_gera_nova_versao_e_antiga_deixa_de_verificar(): void
    {
        $colaborador = $this->criarColaborador();
        $original = $this->criarCertificado($colaborador, $this->criarTreinamento(), $this->criarRh());
        $this->assertSame('valido', $original->resultadoVerificacao()->value);

        $rh = $this->criarRh();
        $this->aceitarTermo($rh);

        $this->actingAs($rh)
            ->post(route('portal.rh.certificados.reemitir', $original))
            ->assertRedirect();

        $original->refresh();
        $nova = Certificado::where('versao', 2)->where('user_id', $colaborador->id)->first();

        $this->assertNotNull($nova);
        $this->assertEquals($nova->id, $original->substituido_por_id); // original aponta p/ nova
        $this->assertNotSame($original->codigo, $nova->codigo);

        $this->assertSame('nao_encontrado', $original->resultadoVerificacao()->value); // RF20: imutável
        $this->assertDatabaseHas(Auditoria::class, ['evento' => 'certificado_reemitido']);
        $this->assertDatabaseHas(Notificacao::class, [
            'user_id' => $colaborador->id,
            'tipo' => 'certificado_reemitido',
        ]);
    }
}
