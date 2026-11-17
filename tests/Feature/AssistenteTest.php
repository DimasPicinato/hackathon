<?php

namespace Tests\Feature;

// RF29–RF34 — assistente público: resposta do runtime, cortesia (RNF08), validação e auditoria (RF33)
use App\Enums\Papel;
use App\Models\AssistenteMensagem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\CriaFixtures;
use Tests\TestCase;

class AssistenteTest extends TestCase
{
    use RefreshDatabase;
    use CriaFixtures;

    /** Simula o runtime OpenCode com as quatro chamadas do contrato (RF30). */
    private function simularRuntime(string $resposta = 'Para verificar um certificado, use a página /verificar.'): void
    {
        Http::fake([
            '*/api/session' => Http::response(['data' => ['id' => 'ses_teste']], 200),
            '*/api/session/ses_teste/prompt' => Http::response(['data' => ['id' => 'msg_prompt_1']], 200),
            '*/api/experimental/session/ses_teste/wait' => Http::response(['data' => []], 200),
            '*/api/session/ses_teste/message' => Http::response(['data' => [
                ['id' => 'msg_prompt_1', 'type' => 'user', 'payload' => ['text' => 'pergunta']],
                ['id' => 'msg_assistant_1', 'type' => 'assistant', 'content' => [['type' => 'text', 'text' => $resposta]]],
                ['type' => 'idle', 'outcome' => 'succeeded'],
            ]], 200),
        ]);
    }

    public function test_widget_recebe_resposta_do_runtime_e_registra_conversa(): void
    {
        $this->simularRuntime();

        $this->postJson(route('assistente.responder'), [
            'sessao' => 'thread-demo-1',
            'pergunta' => 'Onde eu verifico um certificado?',
        ])
            ->assertOk()
            ->assertJson([
                'estado' => 'ok',
                'resposta' => 'Para verificar um certificado, use a página /verificar.',
            ]);

        $this->assertDatabaseHas('assistente_mensagens', [
            'sessao' => 'thread-demo-1',
            'estado' => 'ok',
            'ip' => '127.0.0.1',
        ]); // RF33 — auditoria com IP/data
    }

    public function test_runtime_fora_do_ar_responde_com_cortesia_e_registra_fallback(): void
    {
        Http::fake(['*' => Http::response(['erro' => 'indisponivel'], 500)]);

        $resposta = $this->postJson(route('assistente.responder'), [
            'sessao' => 'thread-demo-2',
            'pergunta' => 'Como funciona a fila de espera?',
        ])
            ->assertOk() // RNF08: a demo nunca recebe 5xx do assistente
            ->assertJson(['estado' => 'fallback'])
            ->json('resposta');

        $this->assertStringContainsString('indisponível', $resposta);
        $this->assertDatabaseHas('assistente_mensagens', [
            'sessao' => 'thread-demo-2',
            'estado' => 'fallback',
        ]);
    }

    public function test_pergunta_vazia_ou_longa_demais_e_recusada(): void
    {
        $this->postJson(route('assistente.responder'), ['sessao' => 't', 'pergunta' => ''])
            ->assertStatus(422);

        $this->postJson(route('assistente.responder'), ['sessao' => 't', 'pergunta' => str_repeat('a', 501)])
            ->assertStatus(422);

        $this->assertDatabaseCount('assistente_mensagens', 0);
    }

    public function test_paginas_publicas_exibem_o_widget(): void
    {
        $this->get('/')->assertOk()->assertSee('Assistente Presenza', false);
        $this->get(route('verificar.busca'))->assertOk()->assertSee('Assistente Presenza', false);
        $this->get(route('login'))->assertOk()->assertSee('Assistente Presenza', false);
    }

    public function test_portal_interno_tambem_exibe_o_widget(): void
    {
        $rh = $this->criarRh();
        $this->aceitarTermo($rh);

        $this->actingAs($rh)
            ->get(route('portal'))
            ->assertOk()
            ->assertSee('Assistente Presenza', false)
            ->assertSee('/assistente', false); // endpoint público usado pelo widget
    }

    public function test_falha_rapida_descarta_sessao_morta_e_repete(): void
    {
        Cache::put('assistente:sessao:thread-retry', 'ses_morto', now()->addMinutes(30));

        Http::fake([
            '*/api/session/ses_morto/prompt' => Http::response(['message' => 'sessão não existe'], 404),
            '*/api/session' => Http::response(['data' => ['id' => 'ses_novo']], 200),
            '*/api/session/ses_novo/prompt' => Http::response(['data' => ['id' => 'msg_prompt_2']], 200),
            '*/api/experimental/session/ses_novo/wait' => Http::response([], 204),
            '*/api/session/ses_novo/message' => Http::response(['data' => [
                ['id' => 'msg_prompt_2', 'type' => 'user'],
                ['id' => 'msg_assistant_2', 'type' => 'assistant', 'content' => [['type' => 'text', 'text' => 'Resposta após recriar a sessão.']]],
            ]], 200),
        ]);

        $this->postJson(route('assistente.responder'), [
            'sessao' => 'thread-retry',
            'pergunta' => 'Como funciona a fila?',
        ])
            ->assertOk()
            ->assertJson([
                'estado' => 'ok',
                'resposta' => 'Resposta após recriar a sessão.',
            ]);

        $this->assertSame('ses_novo', Cache::get('assistente:sessao:thread-retry'));
    }

    /** A3 — erro do gateway do modelo NÃO pode vir como resposta legítima. */
    public function test_erro_do_gateway_vira_cortesia_e_nao_resposta(): void
    {
        Cache::put('assistente:sessao:thread-gateway', 'ses_teste', now()->addMinutes(30));

        Http::fake([
            '*/api/session' => Http::response(['data' => ['id' => 'ses_teste']], 200),
            '*/api/session/ses_teste/prompt' => Http::response(['data' => ['id' => 'msg_prompt_9']], 200),
            '*/api/experimental/session/ses_teste/wait' => Http::response([], 204),
            '*/api/session/ses_teste/message' => Http::response(['data' => [
                ['id' => 'msg_prompt_9', 'type' => 'user'],
                ['id' => 'msg_err', 'type' => 'assistant', 'content' => [['type' => 'text', 'text' => 'Authentication required']]],
            ]], 200),
        ]);

        $resposta = $this->postJson(route('assistente.responder'), [
            'sessao' => 'thread-gateway',
            'pergunta' => 'Onde verifico um certificado?',
        ])
            ->assertOk() // RNF08
            ->assertJson(['estado' => 'fallback'])
            ->json('resposta');

        $this->assertStringContainsString('indisponível', $resposta);
    }

    /** A4 — a resposta tem que ser do turno atual; resposta antiga não vale. */
    public function test_segunda_pergunta_nao_recebe_a_resposta_da_anterior(): void
    {
        Cache::put('assistente:sessao:thread-stale', 'ses_teste', now()->addMinutes(30));

        Http::fake([
            '*/api/session' => Http::response(['data' => ['id' => 'ses_teste']], 200),
            '*/api/session/ses_teste/prompt' => Http::response(['data' => ['id' => 'msg_prompt_2']], 200),
            '*/api/experimental/session/ses_teste/wait' => Http::response([], 204),
            '*/api/session/ses_teste/message' => Http::response(['data' => [
                ['id' => 'msg_prompt_1', 'type' => 'user'],
                ['id' => 'msg_resp_1', 'type' => 'assistant', 'content' => [['type' => 'text', 'text' => 'Resposta ANTIGA']]],
                ['id' => 'msg_prompt_2', 'type' => 'user'],
                // turno atual falhou silenciosamente: nenhuma assistant depois de msg_prompt_2
                ['type' => 'idle', 'outcome' => 'succeeded'],
            ]], 200),
        ]);

        $this->postJson(route('assistente.responder'), [
            'sessao' => 'thread-stale',
            'pergunta' => 'Como funciona a fila de espera?',
        ])
            ->assertOk()
            ->assertJson(['estado' => 'fallback']); // cortesia, nunca a resposta anterior
    }

    /** M3 — trim e formato da sessão na validação server-side. */
    public function test_pergunta_so_com_espacos_e_sessao_malformada_sao_recusadas(): void
    {
        $this->postJson(route('assistente.responder'), ['sessao' => 'thread-ok-123', 'pergunta' => '   '])
            ->assertStatus(422);

        $this->postJson(route('assistente.responder'), ['sessao' => 'thread com espacos!', 'pergunta' => 'Pergunta válida aqui'])
            ->assertStatus(422);

        $this->assertDatabaseCount('assistente_mensagens', 0);
    }

    /** LGPD/Q58 — retenção: purge de conversas com mais de 30 dias. */
    public function test_purge_apaga_apenas_conversas_com_mais_de_30_dias(): void
    {
        $antiga = AssistenteMensagem::forceCreate([
            'sessao' => 'thread-antiga', 'pergunta' => 'p', 'resposta' => 'r', 'estado' => 'ok',
            'ip' => '10.0.0.9', 'created_at' => now()->subDays(31), 'updated_at' => now()->subDays(31),
        ]);
        AssistenteMensagem::forceCreate([
            'sessao' => 'thread-recente', 'pergunta' => 'p', 'resposta' => 'r', 'estado' => 'ok', 'ip' => '10.0.0.9',
        ]);

        $this->artisan('presenza:limpar-assistente')->assertSuccessful();

        $this->assertDatabaseMissing('assistente_mensagens', ['sessao' => 'thread-antiga']);
        $this->assertDatabaseHas('assistente_mensagens', ['sessao' => 'thread-recente']);
    }

    public function test_admin_ve_as_conversas_e_rh_nao(): void
    {
        AssistenteMensagem::create([
            'sessao' => 'thread-auditoria',
            'pergunta' => 'Pergunta registrada',
            'resposta' => 'Resposta registrada',
            'estado' => 'ok',
            'ip' => '203.0.113.10',
        ]);

        $admin = $this->criarRh();
        $admin->darPapel(Papel::Admin);
        $this->aceitarTermo($admin);

        $this->actingAs($admin)
            ->get(route('portal.rh.assistente.conversas'))
            ->assertOk()
            ->assertSee('Pergunta registrada')
            ->assertSee('203.0.113.10');

        $rh = $this->criarRh();
        $this->aceitarTermo($rh);

        $this->actingAs($rh)
            ->get(route('portal.rh.assistente.conversas'))
            ->assertForbidden(); // RF33 — só Admin
    }
}
