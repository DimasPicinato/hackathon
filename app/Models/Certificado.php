<?php

namespace App\Models;

use App\Enums\ResultadoVerificacao;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// RF19–RF22 — imutável: correção gera nova versão (nova linha), nunca altera a original
class Certificado extends Model
{
    protected $fillable = [
        'user_id', 'treinamento_id', 'codigo', 'versao', 'substituido_por_id', 'emitido_por_id',
        'emitido_em', 'carga_horaria', 'concluido_em', 'valido_ate',
    ];

    protected function casts(): array
    {
        return [
            'concluido_em' => 'date',
            'valido_ate' => 'date',
            'emitido_em' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function treinamento(): BelongsTo
    {
        return $this->belongsTo(Treinamento::class);
    }

    public function emitidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'emitido_por_id');
    }

    public function substituidoPor(): BelongsTo
    {
        return $this->belongsTo(self::class, 'substituido_por_id');
    }

    // --- Verificação pública (RF21/RF22) -----------------------------------------

    public function resultadoVerificacao(): ResultadoVerificacao
    {
        if ($this->substituido_por_id !== null) {
            return ResultadoVerificacao::NaoEncontrado; // versão antiga não verifica
        }

        return ($this->valido_ate !== null && $this->valido_ate->isPast())
            ? ResultadoVerificacao::Expirado
            : ResultadoVerificacao::Valido;
    }

    public static function porCodigo(string $codigo): ?self
    {
        return static::query()->where('codigo', $codigo)->first();
    }
}
