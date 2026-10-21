<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Treinamento extends Model
{
    protected $fillable = ['nome', 'descricao', 'carga_horaria', 'validade_meses', 'setor_id', 'ativo'];

    public function setor(): BelongsTo
    {
        return $this->belongsTo(Setor::class);
    }

    public function aulas(): HasMany
    {
        return $this->hasMany(Aula::class)->orderBy('ordem');
    }

    public function certificados(): HasMany
    {
        return $this->hasMany(Certificado::class);
    }
}
