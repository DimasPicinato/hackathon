<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * RF29–RF34 / RNF07 — ponte Laravel → runtime OpenCode (opencode serve).
 *
 * Contrato HTTP provado localmente (OpenCode v2.x):
 *   POST /api/session                         cria a sessão (agente + modelo)
 *   POST /api/session/{id}/prompt             envia a pergunta ({text})
 *   POST /api/experimental/session/{id}/wait  aguarda a conclusão
 *   GET  /api/session/{id}/message            lê as mensagens (content[].text)
 *
 * Auth: HTTP Basic — usuário "opencode" + senha fixa do servidor
 * (OPENCODE_SERVER_PASSWORD no contêiner / ASSISTENTE_SENHA no Laravel).
 */
class RuntimeAssistente
{
    /** Resposta de cortesia quando o runtime/gateway falha (RNF08). */
    public const CORTESIA = 'O assistente está indisponível neste momento. Fale com o RH da Usina ou abra um ticket pelo portal.';

    /** Cria (ou reaproveita) a sessão do runtime para a thread da aba e responde. */
    public function responder(string $sessao, string $pergunta): string
    {
        $sessaoRuntime = $this->sessaoRuntime($sessao);

        $this->post("/api/session/{$sessaoRuntime}/prompt", ['text' => $pergunta]);

        $this->post("/api/experimental/session/{$sessaoRuntime}/wait", []);

        $resposta = $this->ultimaResposta($sessaoRuntime);

        if ($resposta === null) {
            throw new \RuntimeException('Runtime sem resposta de texto.');
        }

        return $resposta;
    }

    /** Reaproveita a sessão do runtime por thread (cache de 30 min); cria quando necessário. */
    private function sessaoRuntime(string $sessao): string
    {
        $chave = "assistente:sessao:{$sessao}";

        $id = Cache::get($chave);

        if (is_string($id) && $id !== '') {
            return $id;
        }

        $resposta = $this->post('/api/session', [
            'title' => 'assistente '.Str::limit($sessao, 12),
            'agent' => config('services.assistente.agente'),
            'model' => $this->modelo(),
        ]);

        $id = $resposta['data']['id'] ?? null;

        if (! is_string($id) || $id === '') {
            throw new \RuntimeException('Runtime não retornou o id da sessão.');
        }

        Cache::put($chave, $id, now()->addMinutes(30));

        return $id;
    }

    /** Última mensagem do assistente com bloco de texto (formato: data[].content[].text). */
    private function ultimaResposta(string $sessaoRuntime): ?string
    {
        $resposta = $this->get("/api/session/{$sessaoRuntime}/message");

        $mensagens = $resposta['data'] ?? [];

        foreach (array_reverse($mensagens) as $mensagem) {
            if (($mensagem['type'] ?? null) !== 'assistant') {
                continue;
            }

            foreach ($mensagem['content'] ?? [] as $bloco) {
                if (($bloco['type'] ?? null) === 'text' && isset($bloco['text']) && trim($bloco['text']) !== '') {
                    return trim($bloco['text']);
                }
            }
        }

        return null;
    }

    /** @return array{id: string, providerID: string} */
    private function modelo(): array
    {
        [$provider, $model] = array_pad(explode('/', (string) config('services.assistente.modelo'), 2), 2, '');

        return ['id' => $model, 'providerID' => $provider];
    }

    private function client()
    {
        return Http::baseUrl((string) config('services.assistente.url'))
            ->withBasicAuth(
                (string) config('services.assistente.usuario'),
                (string) config('services.assistente.senha'),
            )
            ->acceptJson()
            ->timeout((int) config('services.assistente.timeout', 60));
    }

    /** @return array<mixed> */
    private function post(string $caminho, array $dados): array
    {
        return $this->client()
            ->post($caminho, $dados)
            ->throw()
            ->json() ?? [];
    }

    /** @return array<mixed> */
    private function get(string $caminho): array
    {
        return $this->client()
            ->get($caminho)
            ->throw()
            ->json() ?? [];
    }
}
