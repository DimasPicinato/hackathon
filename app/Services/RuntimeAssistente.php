<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * RF29–RF34 / RNF07 — ponte Laravel → runtime OpenCode (opencode serve).
 *
 * Contrato HTTP provado localmente (OpenCode v2.x):
 *   POST /api/session                         cria a sessão (agente + modelo)
 *   POST /api/session/{id}/prompt             envia a pergunta ({text}) e devolve data.id
 *   POST /api/experimental/session/{id}/wait  aguarda a conclusão
 *   GET  /api/session/{id}/message            lê as mensagens (content[].text)
 *
 * Auth: HTTP Basic — usuário "opencode" + senha fixa do servidor
 * (OPENCODE_SERVER_PASSWORD no contêiner / ASSISTENTE_SENHA no Laravel).
 *
 * Integridade da resposta (auditoria 17/11):
 * - só aceitamos texto de assistant gerado DEPOIS do prompt enviado (nada de resposta "antiga");
 * - texto que é claramente erro do gateway (auth/quota/rate limit) vira falha, não resposta;
 * - a sessão runtime é serializada por lock (evita respostas cruzadas em chamadas concorrentes).
 */
class RuntimeAssistente
{
    /** Resposta de cortesia quando o runtime/gateway falha (RNF08). */
    public const CORTESIA = 'O assistente está indisponível neste momento. Fale com o RH da Usina ou abra um ticket pelo portal.';

    /** Textos que indicam erro do gateway do modelo (não são resposta legítima). */
    private const PADROES_ERRO_GATEWAY = [
        'authentication required',
        'unauthorized',
        'api key',
        'rate limit',
        'quota',
        'insufficient',
        'exceeded',
    ];

    /** Tempo de vida do lock por thread (maior que o timeout total do ciclo). */
    private const LOCK_TTL_SEGUNDOS = 130;

    /** Cria (ou reaproveita) a sessão do runtime para a thread da aba e responde. */
    public function responder(string $sessao, string $pergunta): string
    {
        $tentativas = max(1, (int) config('services.assistente.tentativas', 2));
        $chave = $this->chave($sessao);

        for ($tentativa = 1; $tentativa <= $tentativas; $tentativa++) {
            $inicio = microtime(true);

            try {
                return $this->tentarResponder($sessao, $pergunta);
            } catch (\Throwable $e) {
                // Sessão do runtime pode ter morrido (restart/deploy): descarta o cache.
                // Timeout não invalida a sessão — só a espera falhou (ContextException vem
                // do client Http; descartar aqui perderia o contexto sem necessidade).
                $rapida = (microtime(true) - $inicio) < 15;

                if ($rapida && ! $e instanceof ConnectionException) {
                    Cache::forget($chave);
                }

                // Só repete falhas RÁPIDAS (sessão inválida, 401/404). Timeout/latência
                // do modelo não se resolve repetindo — repetir só dobraria a espera.
                if (! $rapida || $tentativa === $tentativas) {
                    throw $e;
                }
            }
        }

        throw new \RuntimeException('Runtime sem resposta.');
    }

    /** Ciclo prompt → wait → leitura, serializado por thread (nada de respostas cruzadas). */
    private function tentarResponder(string $sessao, string $pergunta): string
    {
        $lock = Cache::lock("assistente:lock:{$sessao}", self::LOCK_TTL_SEGUNDOS);

        // Se outra chamada da MESMA thread está em andamento, esperamos a vez dela
        // (evita intercalar prompt/wait/message de duas perguntas na mesma sessão).
        $lock->block(min(115, (int) config('services.assistente.timeout', 100)));

        try {
            $sessaoRuntime = $this->sessaoRuntime($sessao);

            $enviado = $this->post("/api/session/{$sessaoRuntime}/prompt", ['text' => $pergunta]);
            $idPrompt = $enviado['data']['id'] ?? null;

            if (! is_string($idPrompt) || $idPrompt === '') {
                throw new \RuntimeException('Runtime não retornou o id do prompt.');
            }

            $this->post("/api/experimental/session/{$sessaoRuntime}/wait", []);

            $resposta = $this->respostaDoTurno($sessaoRuntime, $idPrompt);

            if ($resposta === null) {
                throw new \RuntimeException('Runtime sem resposta de texto para o turno atual.');
            }

            return $resposta;
        } finally {
            $lock->release();
        }
    }

    private function chave(string $sessao): string
    {
        return "assistente:sessao:{$sessao}";
    }

    /** Reaproveita a sessão do runtime por thread (cache de 30 min); cria quando necessário. */
    private function sessaoRuntime(string $sessao): string
    {
        $chave = $this->chave($sessao);

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

    /**
     * Texto do assistant gerado APENAS para o turno identificado por $idPrompt.
     * Devolve null quando o turno não produziu resposta — nunca devolve resposta de
     * pergunta anterior (stale) nem texto que seja erro do gateway do modelo.
     */
    private function respostaDoTurno(string $sessaoRuntime, string $idPrompt): ?string
    {
        $resposta = $this->get("/api/session/{$sessaoRuntime}/message");

        $mensagens = $resposta['data'] ?? [];

        $indicePrompt = null;

        foreach ($mensagens as $indice => $mensagem) {
            if (($mensagem['id'] ?? null) === $idPrompt) {
                $indicePrompt = $indice;
                break;
            }
        }

        if ($indicePrompt === null) {
            return null;
        }

        foreach (array_slice($mensagens, $indicePrompt + 1) as $mensagem) {
            if (($mensagem['type'] ?? null) !== 'assistant') {
                continue;
            }

            // O contrato pode sinalizar falha do turno no próprio objeto da mensagem.
            if (isset($mensagem['error']) && $mensagem['error'] !== null) {
                return null;
            }

            foreach ($mensagem['content'] ?? [] as $bloco) {
                if (($bloco['type'] ?? null) !== 'text' || ! isset($bloco['text'])) {
                    continue;
                }

                $texto = trim($bloco['text']);

                if ($texto === '') {
                    continue;
                }

                if ($this->pareceErroGateway($texto)) {
                    return null; // erro do provedor não é resposta legítima (RNF08 cobre)
                }

                return $texto;
            }
        }

        return null;
    }

    private function pareceErroGateway(string $texto): bool
    {
        $achatado = strtolower(Str::limit($texto, 300, ''));

        foreach (self::PADROES_ERRO_GATEWAY as $padrao) {
            if (str_contains($achatado, $padrao)) {
                return true;
            }
        }

        return false;
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
            ->timeout((int) config('services.assistente.timeout', 100));
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
