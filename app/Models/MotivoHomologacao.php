<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// RF15/RF26/RF27 — catálogo de motivos de homologação manual
class MotivoHomologacao extends Model
{
    protected $table = 'motivos_homologacao';

    protected $fillable = ['nome', 'ativo', 'criado_por_id'];

    public function criadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'criado_por_id');
    }
}
