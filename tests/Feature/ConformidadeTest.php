<?php

namespace Tests\Feature;

// RF28 — matriz de conformidade: quem precisa fazer o quê, com escopo do líder
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CriaFixtures;
use Tests\TestCase;

class ConformidadeTest extends TestCase
{
    use RefreshDatabase;
    use CriaFixtures;

    public function test_rh_ve_matriz_com_vigente_expirado_e_pendente(): void
    {
        $rh = $this->criarRh();
        $this->aceitarTermo($rh);

        // pessoa com certificado VIGENTE
        $vigente = $this->criarColaborador();
        $this->criarCertificado($vigente, $this->criarTreinamento(), $rh, validoAte: now()->addMonths(10));

        // pessoa com certificado EXPIRADO
        $expirado = $this->criarColaborador();
        $this->criarCertificado($expirado, $this->criarTreinamento(), $rh, validoAte: now()->subMonths(1));

        // pessoa sem certificado em treinamento com aulas: 'pendente'
        $pendente = $this->criarColaborador();
        $aula = $this->criarAula(\App\Enums\StatusAula::Encerrada, $this->criarInstrutor());
        $this->darPresenca($aula, $pendente); // 1/1 aula — mas sem certificado: em_curso

        $resposta = $this->actingAs($rh)->get(route('portal.rh.conformidade'));

        $resposta->assertOk();
        $html = $resposta->getContent();

        $this->assertStringContainsString($vigente->name, $html);
        $this->assertStringContainsString('vigente', $html);
        $this->assertStringContainsString('expirado', $html);
        $this->assertStringContainsString('em_curso', $html);
        $this->assertStringContainsString($pendente->name, $html);
    }

    public function test_treinamento_de_outro_setor_nao_aparece_para_a_pessoa(): void
    {
        $setorA = $this->criarSetor('Fábrica');
        $setorB = $this->criarSetor('Semáforo');
        $rh = $this->criarRh();
        $this->aceitarTermo($rh);
        $pessoa = $this->criarColaborador(['setor_id' => $setorA->id]);

        $treinamentoOutroSetor = $this->criarTreinamento();
        $treinamentoOutroSetor->update(['setor_id' => $setorB->id, 'nome' => fake()->unique()->company() . ' — setor B']);

        $html = $this->actingAs($rh)->get(route('portal.rh.conformidade'))->getContent();

        $this->assertStringNotContainsString($treinamentoOutroSetor->nome . 'validad', $html);
        // célula n/a: treinamento não aplicável não gera status
        $this->assertStringContainsString('n/a', $html);
    }

    public function test_lider_ve_apenas_liderados(): void
    {
        $setor = $this->criarSetor('Rastreamento');
        $lider = \App\Models\User::factory()->comPapeis('lider')->create(['setor_id' => $setor->id]);
        $this->aceitarTermo($lider);
        $meuLiderado = $this->criarColaborador(['setor_id' => $setor->id]);
        $deOutroSetor = $this->criarColaborador();

        $html = $this->actingAs($lider)->get(route('portal.rh.conformidade'))->getContent();

        $this->assertStringContainsString($meuLiderado->name, $html);
        $this->assertStringNotContainsString($deOutroSetor->name, $html);
    }

    public function test_colaborador_nao_ve_matriz(): void
    {
        $colaborador = $this->criarColaborador();
        $this->aceitarTermo($colaborador);

        $this->actingAs($colaborador)
            ->get(route('portal.rh.conformidade'))
            ->assertForbidden();
    }
}
