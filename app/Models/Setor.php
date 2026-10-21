<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Setor extends Model
{
    protected $table = 'setores';

    protected $fillable = ['nome', 'sigla'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function treinamentos(): HasMany
    {
        return $this->hasMany(Treinamento::class);
    }
}
