<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// RF11/RF26 — catálogo de tipos de ticket mantido pelo Admin
class TipoTicket extends Model
{
    protected $table = 'tipos_ticket';

    protected $fillable = ['nome', 'ativo'];
}
