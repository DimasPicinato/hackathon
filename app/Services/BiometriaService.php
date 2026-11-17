<?php

namespace App\Services;

use App\Models\Auditoria;
use App\Models\Biometria;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * RF03/RF14/RNF01 — matching facial real sobre pgvector.
 *
 * O embedding é extraído no NAVEGADOR (face-api.js/128 dimensões — a imagem nunca
 * sai do dispositivo). Aqui só existe comparação vetorial: cosseno entre o vetor
 * capturado e os cadastros ativos com modelo real ('facenet128-web').
 *
 * Embeddings de seed ('mock-facenet-v1') ficam FORA do matching: são vetores
 * aleatórios de demonstração e não correspondem a nenhum rosto real.
 */
class BiometriaService
{
    /** Modelo de embedding real, produzido pelo autocadastro no navegador. */
    public const MODELO = 'facenet128-web';

    /** Modelo de seed (fachada RNF06) — nunca usado para reconhecer. */
    public const MODELO_FACADE = 'mock-facenet-v1';

    /** Dimensões do vetor face-api.js (FaceNet/128-d). */
    public const DIMENSOES = 128;

    /** Similaridade de cosseno mínima para aceitar o match (configurável via .env). */
    public function limiar(): float
    {
        return (float) config('services.biometria.limiar', 0.80);
    }

    /**
     * Autocadastro/renovação do rosto (RF03/RNF01): substitui o embedding ativo
     * pela captura real e registra a auditoria da captura. Nenhuma imagem é
     * armazenada — apenas o vetor.
     */
    public function cadastrar(User $user, array $embedding, ?int $atorId = null, ?string $ip = null): Biometria
    {
        $this->garantirDimensoes($embedding);

        $vetor = $this->vetorLiteral($embedding);

        $existente = Biometria::where('user_id', $user->id)->first();

        if ($existente) {
            DB::statement(
                'UPDATE biometrias SET embedding = ?::vector, modelo = ?, ativo = true, capturado_em = NOW(), updated_at = NOW() WHERE id = ?',
                [$vetor, self::MODELO, $existente->id],
            );
            $biometria = $existente->refresh();
        } else {
            DB::statement(
                'INSERT INTO biometrias (user_id, embedding, modelo, ativo, capturado_em, created_at, updated_at) VALUES (?, ?::vector, ?, true, NOW(), NOW(), NOW())',
                [$user->id, $vetor, self::MODELO],
            );
            $biometria = Biometria::where('user_id', $user->id)->firstOrFail();
        }

        Auditoria::registrar('biometria_cadastro', $biometria, [
            'modelo' => self::MODELO,
            'renovacao' => (bool) $existente,
        ], $atorId ?? $user->id, $ip); // RNF01 — captura de biometria é evento sensível

        return $biometria;
    }

    /**
     * Identificação 1:N: rosto capturado → pessoa cadastrada (modelo real).
     * Devolve ['user' => User, 'similaridade' => float] ou null quando ninguém
     * passa o limiar.
     *
     * @return array{user: User, similaridade: float}|null
     */
    public function identificar(array $embedding): ?array
    {
        $this->garantirDimensoes($embedding);
        $vetor = $this->vetorLiteral($embedding);
        $limiar = $this->limiar();

        $linha = DB::selectOne(
            'SELECT b.user_id, 1 - (b.embedding <=> ?::vector) AS similaridade
               FROM biometrias b
              WHERE b.ativo = true AND b.modelo = ?
              ORDER BY b.embedding <=> ?::vector
              LIMIT 1',
            [$vetor, self::MODELO, $vetor],
        );

        if (! $linha || (float) $linha->similaridade < $limiar) {
            return null;
        }

        $user = User::find($linha->user_id);

        if (! $user) {
            return null;
        }

        return ['user' => $user, 'similaridade' => (float) $linha->similaridade];
    }

    /**
     * Conferência 1:1 (via instrutor, RF14.b): o rosto capturado confere com o
     * cadastro do colaborador indicado? Devolve a similaridade ou null.
     */
    public function conferir(User $alvo, array $embedding): ?float
    {
        $this->garantirDimensoes($embedding);
        $vetor = $this->vetorLiteral($embedding);

        $linha = DB::selectOne(
            'SELECT 1 - (embedding <=> ?::vector) AS similaridade
               FROM biometrias
              WHERE user_id = ? AND ativo = true AND modelo = ?',
            [$vetor, $alvo->id, self::MODELO],
        );

        if (! $linha) {
            return null; // sem cadastro real do alvo
        }

        $similaridade = (float) $linha->similaridade;

        return $similaridade >= $this->limiar() ? $similaridade : null;
    }

    /** O usuário já tem embedding REAL (não-fachada) cadastrado? */
    public function temCadastroReal(User $user): bool
    {
        return Biometria::where('user_id', $user->id)
            ->where('modelo', self::MODELO)
            ->where('ativo', true)
            ->exists();
    }

    /** @return float[] */
    private function vetorLiteral(array $embedding): string
    {
        return '['.implode(',', array_map(fn ($v) => sprintf('%.7F', (float) $v), $embedding)).']';
    }

    private function garantirDimensoes(array $embedding): void
    {
        if (count($embedding) !== self::DIMENSOES) {
            throw new \InvalidArgumentException('Embedding deve ter '.self::DIMENSOES.' dimensões.');
        }
    }
}
