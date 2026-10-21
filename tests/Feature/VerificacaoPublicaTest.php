<?php

namespace Tests\Feature;

// RF21, RF22 — verificação pública: QR/código/link, mínimo de dados (LGPD), PDF
use App\Models\Verificacao;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CriaFixtures;
use Tests\TestCase;

class VerificacaoPublicaTest extends TestCase
{
    use RefreshDatabase;
    use CriaFixtures;

    public function test_codigo_valido_exibe_status_e_minimo_de_dados(): void
    {
        $instrutor = $this->criarInstrutor();
        $aula = $this->criarAula();
        $colaborador = $this->criarColaborador();

        $certificado = $this->criarCertificado($colaborador, $aula->treinamento, $instrutor);

        $this->get(route('verificar.mostrar', $certificado->codigo))
            ->assertOk()
            ->assertSee('VÁLIDO')
            ->assertSee($colaborador->name)
            ->assertSee($aula->treinamento->nome)
            ->assertSee($certificado->valido_ate->format('d/m/Y'))
            ->assertDontSee((string) $colaborador->matricula); // LGPD: mínimo de dados
    }

    public function test_codigo_expirado_exibe_expirado(): void
    {
        $colaborador = $this->criarColaborador();
        $treinamento = $this->criarTreinamento();
        $this->criarCertificado($colaborador, $treinamento,
            validoAte: now()->subMonths(6)); // RF22: validade alimenta EXPIRADO

        $codigo = \App\Models\Certificado::where('user_id', $colaborador->id)->value('codigo');

        $this->get(route('verificar.mostrar', $codigo))
            ->assertOk()
            ->assertSee('EXPIRADO');
    }

    public function test_codigo_inexistente_exibe_nao_encontrado(): void
    {
        $this->get(route('verificar.mostrar', 'PZ-9999-999999'))
            ->assertOk()
            ->assertSee('NÃO ENCONTRADO');
    }

    public function test_cada_verificacao_fica_no_log(): void
    {
        $colaborador = $this->criarColaborador();
        $cert = $this->criarCertificado($colaborador, $this->criarTreinamento());

        $this->get(route('verificar.mostrar', $cert->codigo))->assertOk();
        $this->get(route('verificar.mostrar', 'PZ-INEXISTENTE-1'))->assertOk();

        $this->assertDatabaseHas(Verificacao::class, [
            'codigo' => $cert->codigo,
            'resultado' => 'valido',
        ]);
        $this->assertDatabaseHas(Verificacao::class, [
            'codigo' => 'PZ-INEXISTENTE-1',
            'resultado' => 'nao_encontrado',
        ]); // RNF01: trilha da verificação pública
    }

    public function test_pdf_do_certificado_baixavel_publicamente(): void
    {
        $colaborador = $this->criarColaborador();
        $cert = $this->criarCertificado($colaborador, $this->criarTreinamento());

        $this->get(route('verificar.pdf', $cert->codigo))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
