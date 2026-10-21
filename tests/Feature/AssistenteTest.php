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
            '*/api/session/ses_teste/prompt' => Http::response(['data' => ['id' => 'msg_teste']], 200),
            '*/api/experimental/session/ses_teste/wait' => Http::response(['data' => []], 200),
            '*/api/session/ses_teste/message' => Http::response(['data' => [
                ['type' => 'user', 'payload' => ['text' => 'pergunta']],
                ['type' => 'assistant', 'content' => [['type' => 'text', 'text' => $resposta]]],
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
            '*/api/session/ses_novo/prompt' => Http::response(['data' => ['id' => 'msg_1']], 200),
            '*/api/experimental/session/ses_novo/wait' => Http::response([], 204),
            '*/api/session/ses_novo/message' => Http::response(['data' => [
                ['type' => 'assistant', 'content' => [['type' => 'text', 'text' => 'Resposta após recriar a sessão.']]],
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
