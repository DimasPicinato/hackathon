<?php

namespace Tests\Feature;

// RF28 — relatórios por sessão e por pessoa, CSV baixável; colaborador não tem acesso
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CriaFixtures;
use Tests\TestCase;

class RelatorioCsvTest extends TestCase
{
    use RefreshDatabase;
    use CriaFixtures;

    public function test_csv_da_sessao_lista_insritos_com_metodo_de_presenca(): void
    {
        $rh = $this->criarRh();
        $this->aceitarTermo($rh);
        $aula = $this->criarAula(\App\Enums\StatusAula::Encerrada, $this->criarInstrutor());
        $presente = $this->criarColaborador();
        $ausente = $this->criarColaborador();
        $this->inscreverComStatus($aula, $presente);
        $this->inscreverComStatus($aula, $ausente);
        $this->darPresenca($aula, $presente, 'manual');

        $resposta = $this->actingAs($rh)
            ->get(route('portal.rh.relatorios.sessao', $aula));

        $resposta->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $csv = $resposta->streamedContent();
        $this->assertStringContainsString($aula->treinamento->nome, $csv);
        $this->assertStringContainsString($presente->name, $csv);
        $this->assertStringContainsString('manual', $csv);
        $this->assertStringContainsString($ausente->name, $csv);
        $this->assertStringContainsString('ausente', $csv);
    }

    public function test_csv_de_pessoas_traz_presencas_e_certificados(): void
    {
        $rh = $this->criarRh();
        $this->aceitarTermo($rh);
        $aula = $this->criarAula(\App\Enums\StatusAula::Encerrada, $this->criarInstrutor());
        $colaborador = $this->criarColaborador();
        $this->darPresenca($aula, $colaborador);
        $this->criarCertificado($colaborador, $aula->treinamento, $rh);

        $resposta = $this->actingAs($rh)
            ->get(route('portal.rh.relatorios.pessoas'));

        $resposta->assertOk();
        $csv = $resposta->streamedContent();
        $this->assertStringContainsString($colaborador->name, $csv);
        $this->assertStringContainsString($colaborador->matricula, $csv);
    }

    public function test_colaborador_nao_baixa_relatorios(): void
    {
        $colaborador = $this->criarColaborador();
        $this->aceitarTermo($colaborador);

        $this->actingAs($colaborador)
            ->get(route('portal.rh.relatorios'))
            ->assertForbidden();

        $this->actingAs($colaborador)
            ->get(route('portal.rh.relatorios.pessoas'))
            ->assertForbidden();
    }

    public function test_lider_ve_relatorio_restrito_ao_setor(): void
    {
        $setor = $this->criarSetor('Automação');
        $lider = \App\Models\User::factory()->comPapeis('lider')->create(['setor_id' => $setor->id]);
        $this->aceitarTermo($lider);
        $ulado = $this->criarColaborador(['setor_id' => $setor->id]);
        $outraPessoa = $this->criarColaborador(); // outro setor
        $aula = $this->criarAula(\App\Enums\StatusAula::Encerrada, $this->criarInstrutor());
        $this->darPresenca($aula, $ulado);
        $this->darPresenca($aula, $outraPessoa);

        $this->actingAs($lider)
            ->get(route('portal.rh.relatorios'))
            ->assertOk();                         // sessões visíveis

        $resposta = $this->actingAs($lider)
            ->get(route('portal.rh.relatorios.pessoas'));

        $resposta->assertOk();
        $csv = $resposta->streamedContent();
        $this->assertStringContainsString($ulado->name, $csv);
        $this->assertStringNotContainsString($outraPessoa->name, $csv); // pessoas: só o próprio setor
    }
}
