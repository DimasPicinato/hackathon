<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// RF23/RF24 — canal in-app
class Notificacao extends Model
{
    protected $table = 'notificacoes';

    protected $fillable = ['user_id', 'titulo', 'corpo', 'tipo', 'link', 'lida_em'];

    protected function casts(): array
    {
        return [
            'lida_em' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function marcarComoLida(): void
    {
        if ($this->lida_em === null) {
            $this->update(['lida_em' => now()]);
        }
    }
}
