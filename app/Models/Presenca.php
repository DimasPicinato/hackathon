<?php

namespace App\Models;

use App\Enums\MetodoPresenca;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// RF14–RF17 — presença facial ou homologada, sempre com autor e horário
class Presenca extends Model
{
    protected $fillable = [
        'aula_id', 'user_id', 'metodo', 'motivo_id',
        'registrado_por_id', 'senha_colaborador_validada', 'observacao',
    ];

    protected function casts(): array
    {
        return [
            'metodo' => MetodoPresenca::class,
            'senha_colaborador_validada' => 'boolean',
        ];
    }

    public function aula(): BelongsTo
    {
        return $this->belongsTo(Aula::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function motivo(): BelongsTo
    {
        return $this->belongsTo(MotivoHomologacao::class, 'motivo_id');
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por_id');
    }
}
