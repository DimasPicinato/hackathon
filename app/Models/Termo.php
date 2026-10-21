<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// RF04 — versões do termo LGPD de biometria
class Termo extends Model
{
    protected $fillable = ['versao', 'titulo', 'conteudo', 'ativo'];

    public function aceites(): HasMany
    {
        return $this->hasMany(TermoAceite::class);
    }

    public static function vigente(): ?self
    {
        return static::query()->where('ativo', true)->latest('id')->first();
    }
}
