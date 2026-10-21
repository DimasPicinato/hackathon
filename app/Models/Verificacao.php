<?php

namespace App\Models;

use App\Enums\ResultadoVerificacao;
use Illuminate\Database\Eloquent\Model;

// RF21/RNF01 — log da verificação pública (mínimo de dados)
class Verificacao extends Model
{
    protected $table = 'verificacoes';

    protected $fillable = ['codigo', 'resultado', 'ip', 'user_agent'];

    protected function casts(): array
    {
        return [
            'resultado' => ResultadoVerificacao::class,
        ];
    }
}
