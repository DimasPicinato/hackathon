<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TermoAceite extends Model
{
    protected $table = 'termo_aceites';

    protected $fillable = ['user_id', 'termo_id', 'ip'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function termo(): BelongsTo
    {
        return $this->belongsTo(Termo::class);
    }
}
