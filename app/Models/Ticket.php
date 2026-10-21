<?php

namespace App\Models;

use App\Enums\StatusTicket;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// RF10–RF12 — cancelamento/justificativa passam por ticket
class Ticket extends Model
{
    protected $fillable = [
        'user_id', 'treinamento_id', 'tipo_ticket_id', 'texto',
        'status', 'resolvido_por_id', 'resolvido_em', 'resultado',
    ];

    protected function casts(): array
    {
        return [
            'status' => StatusTicket::class,
            'resolvido_em' => 'datetime',
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

    public function tipoTicket(): BelongsTo
    {
        return $this->belongsTo(TipoTicket::class, 'tipo_ticket_id');
    }

    public function resolvidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolvido_por_id');
    }
}
