<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Metadados biométricos (embedding pgvector) — RF03/SEED01
class Biometria extends Model
{
    protected $fillable = ['user_id', 'modelo', 'ativo'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
