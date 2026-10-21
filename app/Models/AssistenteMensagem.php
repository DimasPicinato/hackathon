<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// RF33 — conversa do Assistente público (visível somente ao Admin)
class AssistenteMensagem extends Model
{
    protected $table = 'assistente_mensagens';

    protected $fillable = [
        'sessao',
        'pergunta',
        'resposta',
        'estado',
        'ip',
    ];
}
