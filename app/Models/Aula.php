<?php

namespace App\Models;

use App\Enums\StatusAula;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// RF03/RF06/RF14 — janela controlada pelo instrutor; facial só com aula em andamento
class Aula extends Model
{
    protected $fillable = [
        'treinamento_id', 'instrutor_id', 'ordem', 'titulo', 'data_inicio', 'data_fim',
        'local', 'vagas', 'status', 'iniciada_em', 'encerrada_em',
    ];

    protected function casts(): array
    {
        return [
            'data_inicio' => 'datetime',
            'data_fim' => 'datetime',
            'iniciada_em' => 'datetime',
            'encerrada_em' => 'datetime',
            'status' => StatusAula::class,
        ];
    }

    public function treinamento(): BelongsTo
    {
        return $this->belongsTo(Treinamento::class);
    }

    public function instrutor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instrutor_id');
    }

    public function inscricoes(): HasMany
    {
        return $this->hasMany(Inscricao::class);
    }

    public function presencas(): HasMany
    {
        return $this->hasMany(Presenca::class);
    }

    public function justificativas(): HasMany
    {
        return $this->hasMany(Justificativa::class);
    }

    // --- Janela (RF03) ------------------------------------------------------------

    public function estaAtiva(): bool
    {
        return $this->status === StatusAula::EmAndamento;
    }

    public function vagasRestantes(): int
    {
        return max(0, $this->vagas - $this->inscricoes()->where('status', 'reservada')->count());
    }
}
