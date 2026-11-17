<?php

namespace Tests\Feature;

// RF03, RF14, RF16, RF04 — chamada por reconhecimento facial REAL (matching pgvector)
use App\Enums\StatusAula;
use App\Models\Auditoria;
use App\Models\Aula;
use App\Models\Presenca;
use App\Services\BiometriaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CriaFixtures;
use Tests\TestCase;

class PresencaFacialTest extends TestCase
{
    use RefreshDatabase;
    use CriaFixtures;

    /**
     * Vetor determinista de 128 dimensões: e_$indice (base ortonormal).
     * Mesmo índice = mesma "pessoa" (cosseno 1.0, acima do limiar 0,80);
     * índices distintos são ortogonais (cosseno 0.0, recusados).
     *
     * @return float[]
     */
    private function embedding(int $indice): array
    {
        $vetor = array_fill(0, 128, 0.0);
        $vetor[$indice % 128] = 1.0;

        return $vetor;
    }

    private function cadastrarRosto($user, int $indice): void
    {
        app(BiometriaService::class)->cadastrar($user, $this->embedding($indice));
    }

    public function test_colaborador_aponta_a_propria_presenca_com_aula_ativa(): void
    {
        $aula = $this->criarAula(StatusAula::EmAndamento, $this->criarInstrutor());
        $colaborador = $this->criarColaborador();
        $this->aceitarTermo($colaborador);
        $this->cadastrarRosto($colaborador, 1); // enrollment real antes da presença

        $this->actingAs($colaborador)
            ->post(route('portal.presenca.facial', [
                'aula_id' => $aula->id,
                'embedding' => $this->embedding(1), // mesmo rosto cadastrado
            ]))
            ->assertRedirect(route('portal'));

        $this->assertDatabaseHas(Presenca::class, [
            'aula_id' => $aula->id,
            'user_id' => $colaborador->id,
            'metodo' => 'facial',
        ]);
        $this->assertDatabaseHas(Auditoria::class, ['evento' => 'presenca_facial']);
    }

    public function test_fora_da_janela_nao_registra_facial(): void
    {
        foreach ([StatusAula::Agendada, StatusAula::Encerrada] as $status) {
            $aula = $this->criarAula($status, $this->criarInstrutor());
            $colaborador = $this->criarColaborador();
            $this->aceitarTermo($colaborador);
            $this->cadastrarRosto($colaborador, 1);

            $this->actingAs($colaborador)
                ->post(route('portal.presenca.facial', [
                    'aula_id' => $aula->id,
                    'embedding' => $this->embedding(1),
                ]))
                ->assertSessionHasErrors('aula'); // RF03: só com instrutor iniciou e não encerrou

            $this->assertDatabaseMissing(Presenca::class, ['user_id' => $colaborador->id]);
        }
    }

    public function test_sem_aceite_de_termo_vai_para_tela_do_termo(): void
    {
        $aula = $this->criarAula(StatusAula::EmAndamento, $this->criarInstrutor());
        $colaborador = $this->criarColaborador(); // sem aceite (RF04)
        $this->cadastrarRosto($colaborador, 1);

        $this->actingAs($colaborador)
            ->post(route('portal.presenca.facial', [
                'aula_id' => $aula->id,
                'embedding' => $this->embedding(1),
            ]))
            ->assertRedirect(route('termo.mostrar'));

        $this->assertDatabaseMissing(Presenca::class, ['aula_id' => $aula->id]);
    }

    public function test_duplicidade_e_recusada_e_fica_auditoria(): void
    {
        $aula = $this->criarAula(StatusAula::EmAndamento, $this->criarInstrutor());
        $colaborador = $this->criarColaborador();
        $this->aceitarTermo($colaborador);
        $this->cadastrarRosto($colaborador, 1);
        Presenca::create(['aula_id' => $aula->id, 'user_id' => $colaborador->id, 'metodo' => 'facial']);

        $this->actingAs($colaborador)
            ->post(route('portal.presenca.facial', [
                'aula_id' => $aula->id,
                'embedding' => $this->embedding(1),
            ]))
            ->assertSessionHasErrors('duplicidade'); // RF16: 1 presença por pessoa por aula

        $this->assertDatabaseHas(Auditoria::class, ['evento' => 'presenca_duplicada']);
        $this->assertEquals(1, Presenca::where('aula_id', $aula->id)->count());
    }

    public function test_instrutor_aponta_facial_para_o_colaborador(): void
    {
        $instrutor = $this->criarInstrutor();
        $aula = $this->criarAula(StatusAula::EmAndamento, $instrutor);
        $this->aceitarTermo($instrutor);
        $colaborador = $this->criarColaborador();
        $this->aceitarTermo($colaborador);
        $this->cadastrarRosto($colaborador, 3);

        $this->actingAs($instrutor)
            ->post(route('portal.aula.presenca.facial', ['aula' => $aula]), [
                'colaborador_id' => $colaborador->id,
                'embedding' => $this->embedding(3),
            ])
            ->assertRedirect(route('portal.aula.mostrar', $aula)); // RF14: via instrutor

        $this->assertDatabaseHas(Presenca::class, [
            'aula_id' => $aula->id,
            'user_id' => $colaborador->id,
            'metodo' => 'facial',
        ]);
    }

    public function test_instrutor_de_outra_aula_nao_aponta_presenca(): void
    {
        $aula = $this->criarAula(StatusAula::EmAndamento, $this->criarInstrutor());
        $outroInstrutor = $this->criarInstrutor();
        $this->aceitarTermo($outroInstrutor);
        $colaborador = $this->criarColaborador();
        $this->aceitarTermo($colaborador);
        $this->cadastrarRosto($colaborador, 1);

        $this->actingAs($outroInstrutor)
            ->post(route('portal.aula.presenca.facial', ['aula' => $aula]), [
                'colaborador_id' => $colaborador->id,
                'embedding' => $this->embedding(1),
            ])
            ->assertForbidden(); // RF06: instrutor conduz apenas SUAS aulas
    }

    // ── Matching real (pgvector, cosseno >= limiar) ──────────────────────────────

    public function test_rosto_sem_cadastro_acima_do_limiar_e_recusado_com_auditoria(): void
    {
        $aula = $this->criarAula(StatusAula::EmAndamento, $this->criarInstrutor());
        $colaborador = $this->criarColaborador();
        $this->aceitarTermo($colaborador);
        $this->cadastrarRosto($colaborador, 1); // cadastro real, mas rosto capturado é outro

        $this->actingAs($colaborador)
            ->post(route('portal.presenca.facial', [
                'aula_id' => $aula->id,
                'embedding' => $this->embedding(7), // rosto que não casa com ninguém
            ]))
            ->assertSessionHasErrors('facial'); // "não reconhecido → homologação manual"

        $this->assertDatabaseMissing(Presenca::class, ['aula_id' => $aula->id, 'user_id' => $colaborador->id]);
        $this->assertDatabaseHas(Auditoria::class, ['evento' => 'presenca_facial_rejeitada']);
    }

    public function test_sem_embedding_a_requisicao_e_validada_como_invalida(): void
    {
        $aula = $this->criarAula(StatusAula::EmAndamento, $this->criarInstrutor());
        $colaborador = $this->criarColaborador();
        $this->aceitarTermo($colaborador);

        $this->actingAs($colaborador)
            ->post(route('portal.presenca.facial', ['aula_id' => $aula->id]))
            ->assertSessionHasErrors('embedding'); // a fachada antiga aceitava; agora é obrigatório

        $this->assertDatabaseMissing(Presenca::class, ['aula_id' => $aula->id]);
    }

    public function test_divergencia_reivindicado_contra_reconhecido_e_recusada(): void
    {
        $instrutor = $this->criarInstrutor();
        $aula = $this->criarAula(StatusAula::EmAndamento, $instrutor);
        $this->aceitarTermo($instrutor);

        $reivindicado = $this->criarColaborador(); // aula B aponta o rosto de A (RF16)
        $outro = $this->criarColaborador();
        $this->aceitarTermo($reivindicado);
        $this->aceitarTermo($outro);
        $this->cadastrarRosto($reivindicado, 1);
        $this->cadastrarRosto($outro, 2);

        $this->actingAs($instrutor)
            ->post(route('portal.aula.presenca.facial', ['aula' => $aula]), [
                'colaborador_id' => $reivindicado->id,
                'embedding' => $this->embedding(2), // reconhece "outro", não o reivindicado
            ])
            ->assertSessionHasErrors('facial');

        $this->assertDatabaseMissing(Presenca::class, ['aula_id' => $aula->id]);
        $this->assertDatabaseHas(Auditoria::class, ['evento' => 'presenca_facial_divergente']);
    }

    public function test_embedding_de_seed_mock_nao_reconhece_ninguem(): void
    {
        $aula = $this->criarAula(StatusAula::EmAndamento, $this->criarInstrutor());
        $colaborador = $this->criarColaborador();
        $this->aceitarTermo($colaborador);

        // seed antigo (fachada RNF06): vetor e_1 com modelo mock-facenet-v1
        \Illuminate\Support\Facades\DB::statement(
            'INSERT INTO biometrias (user_id, embedding, modelo, ativo, capturado_em, created_at, updated_at)
             VALUES (?, ?::vector, ?, true, NOW(), NOW(), NOW())',
            [$colaborador->id, '['.implode(',', $this->embedding(1)).']', 'mock-facenet-v1'],
        );

        $this->actingAs($colaborador)
            ->post(route('portal.presenca.facial', [
                'aula_id' => $aula->id,
                'embedding' => $this->embedding(1), // rosto idêntico ao vetor de seed
            ]))
            ->assertSessionHasErrors('facial'); // vetores de seed ficam FORA do matching

        $this->assertDatabaseMissing(Presenca::class, ['aula_id' => $aula->id]);
    }

    // ── Enrollment (RF03/RNF01 — autocadastro /portal/biometria) ─────────────────

    public function test_autocadastro_grava_embedding_real_e_audita_captura(): void
    {
        $colaborador = $this->criarColaborador();
        $this->aceitarTermo($colaborador);

        $this->actingAs($colaborador)
            ->post(route('portal.biometria.cadastrar'), ['embedding' => $this->embedding(5)])
            ->assertRedirect(route('portal.biometria.mostrar'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('biometrias', [
            'user_id' => $colaborador->id,
            'modelo' => BiometriaService::MODELO,
            'ativo' => true,
        ]);
        $this->assertDatabaseHas(Auditoria::class, ['evento' => 'biometria_cadastro']);
    }

    public function test_renovacao_substitui_o_vetor_sem_duplicar(): void
    {
        $colaborador = $this->criarColaborador();
        $this->aceitarTermo($colaborador);
        $this->cadastrarRosto($colaborador, 1);

        $this->actingAs($colaborador)
            ->post(route('portal.biometria.cadastrar'), ['embedding' => $this->embedding(9)])
            ->assertRedirect(route('portal.biometria.mostrar'));

        $this->assertEquals(1, \Illuminate\Support\Facades\DB::table('biometrias')
            ->where('user_id', $colaborador->id)->count());
        $this->assertDatabaseHas(Auditoria::class, ['evento' => 'biometria_cadastro']);
    }

    public function test_tela_de_biometria_mostra_status_do_cadastro(): void
    {
        $colaborador = $this->criarColaborador();
        $this->aceitarTermo($colaborador);

        $this->actingAs($colaborador)
            ->get(route('portal.biometria.mostrar'))
            ->assertOk()
            ->assertSee('Nenhum rosto cadastrado');
    }
}
