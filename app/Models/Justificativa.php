<?php

namespace App\Models;

use App\Enums\StatusJustificativa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// RF12/RF13 — status alterável a qualquer momento por RH/Líder
class Justificativa extends Model
{
    protected $fillable = [
        'user_id', 'aula_id', 'ticket_id', 'status', 'texto', 'alterado_por_id', 'alterado_em',
    ];

    protected function casts(): array
    {
        return [
            'status' => StatusJustificativa::class,
            'alterado_em' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function aula(): BelongsTo
    {
        return $this->belongsTo(Aula::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function alteradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'alterado_por_id');
    }
}
