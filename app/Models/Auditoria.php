<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

// RNF01/RNF03 — trilha de auditoria de eventos sensíveis
class Auditoria extends Model
{
    protected $table = 'auditorias';

    protected $fillable = ['user_id', 'evento', 'sujeito_type', 'sujeito_id', 'dados', 'ip'];

    protected function casts(): array
    {
        return [
            'dados' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sujeito(): MorphTo
    {
        return $this->morphTo();
    }

    public static function registrar(
        string $evento,
        ?Model $sujeito = null,
        array $dados = [],
        ?int $atorId = null,
        ?string $ip = null,
    ): self {
        return static::query()->create([
            'user_id' => $atorId,
            'evento' => $evento,
            'sujeito_type' => $sujeito?->getMorphClass(),
            'sujeito_id' => $sujeito?->getKey(),
            'dados' => $dados,
            'ip' => $ip,
        ]);
    }
}
