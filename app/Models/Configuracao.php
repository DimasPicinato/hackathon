<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// RF23/RF26 — configurações do sistema (SMTP etc.), painel do Admin
class Configuracao extends Model
{
    protected $table = 'configuracoes';

    protected $fillable = ['chave', 'valor', 'atualizado_por_id'];

    public function atualizadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atualizado_por_id');
    }

    public static function valor(string $chave, ?string $padrao = null): ?string
    {
        return static::query()->where('chave', $chave)->value('valor') ?? $padrao;
    }
}
