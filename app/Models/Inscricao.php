<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// RF08/RF09 — vaga nominal + fila de espera
class Inscricao extends Model
{
    protected $table = 'inscricoes';

    protected $fillable = ['aula_id', 'user_id', 'status', 'posicao_fila', 'reservado_por_id'];

    public function aula(): BelongsTo
    {
        return $this->belongsTo(Aula::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reservadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reservado_por_id');
    }
}
