<?php

namespace App\Services;

use App\Models\Auditoria;
use App\Models\Certificado;
use App\Models\Notificacao;
use App\Models\Treinamento;
use App\Models\User;
use Illuminate\Validation\ValidationException;

// RF18–RF22 — emissão manual pelo RH, imutável, validade por treinamento
class CertificadoService
{
    public static function elegivel(User $colaborador, Treinamento $treinamento): bool
    {
        $aulas = $treinamento->aulas;

        // RF18: presença em TODAS as aulas do treinamento (falta justificada = sem certificado)
        return $aulas->isNotEmpty()
            && $aulas->every(fn ($aula) => $aula->presencas()
                ->where('user_id', $colaborador->id)->exists());
    }

    public static function emitir(User $colaborador, Treinamento $treinamento, User $rh, ?string $ip = null): Certificado
    {
        if (! static::elegivel($colaborador, $treinamento)) {
            throw ValidationException::withMessages([
                'elegibilidade' => "{$colaborador->name} não compareceu a todas as aulas — sem certificado (RF18).",
            ]);
        }

        $ultimaAula = \Carbon\Carbon::parse($treinamento->aulas()->max('data_inicio'));

        $certificado = Certificado::create([
            'user_id' => $colaborador->id,
            'treinamento_id' => $treinamento->id,
            'codigo' => static::gerarCodigo(),
            'versao' => 1,
            'emitido_por_id' => $rh->id,
            'emitido_em' => now(),
            'carga_horaria' => $treinamento->carga_horaria,
            'concluido_em' => $ultimaAula->toDateString(), // última aula
            'valido_ate' => $treinamento->validade_meses
                ? $ultimaAula->copy()->addMonths($treinamento->validade_meses)->toDateString()
                : null, // RF22
        ]);

        Auditoria::registrar('certificado_emitido', $certificado, [
            'colaborador' => $colaborador->matricula,
            'treinamento' => $treinamento->nome,
        ], $rh->id, $ip);

        Notificacao::create([ // RF24: certificado emitido
            'user_id' => $colaborador->id,
            'titulo' => 'Certificado emitido!',
            'corpo' => "{$treinamento->nome}: código {$certificado->codigo}. Verifique no site público pelo QR ou link.",
            'tipo' => 'certificado_emitido',
        ]);

        return $certificado;
    }

    /** RF20 — imutável: correção/reemissão gera NOVA versão, nunca altera a original. */
    public static function reemitir(Certificado $original, User $rh, ?string $ip = null): Certificado
    {
        $novo = Certificado::create([
            'user_id' => $original->user_id,
            'treinamento_id' => $original->treinamento_id,
            'codigo' => static::gerarCodigo(),
            'versao' => $original->versao + 1,
            'emitido_por_id' => $rh->id,
            'emitido_em' => now(),
            'carga_horaria' => $original->carga_horaria ?? $original->treinamento->carga_horaria,
            'concluido_em' => $original->concluido_em,
            'valido_ate' => $original->valido_ate,
        ]);

        $original->update(['substituido_por_id' => $novo->id]); // original vira registro histórico

        Auditoria::registrar('certificado_reemitido', $novo, [
            'original' => $original->codigo,
            'novo' => $novo->codigo,
            'versao' => $novo->versao,
        ], $rh->id, $ip);

        Notificacao::create([
            'user_id' => $original->user_id,
            'titulo' => 'Certificado reemitido (nova versão)',
            'corpo' => "Use sempre o código mais recente: {$novo->codigo}. O original ({$original->codigo}) não é mais verificável.",
            'tipo' => 'certificado_reemitido',
        ]);

        return $novo;
    }

    private static function gerarCodigo(): string
    {
        do {
            $codigo = 'PZ-' . now()->format('Y') . '-' . str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (Certificado::query()->where('codigo', $codigo)->exists());

        return $codigo;
    }
}
