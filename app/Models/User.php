<?php

namespace App\Models;

use App\Enums\Papel;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'matricula', 'setor_id', 'turno', 'ativo', 'papeis'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'papeis' => 'array',
            'ativo' => 'boolean',
        ];
    }

    // --- RF02: papéis acumuláveis -------------------------------------------------

    /** @param Papel|string $papel */
    public function temPapel(Papel|string $papel): bool
    {
        $valor = $papel instanceof Papel ? $papel->value : $papel;

        return in_array($valor, $this->papeis ?? [], true);
    }

    public function darPapel(Papel $papel): void
    {
        if (! $this->temPapel($papel)) {
            $this->papeis = [...($this->papeis ?? []), $papel->value];
            $this->save();
        }
    }

    public function removerPapel(Papel $papel): void
    {
        $this->papeis = array_values(array_diff($this->papeis ?? [], [$papel->value]));
        $this->save();
    }

    // --- Relações ----------------------------------------------------------------

    public function setor(): BelongsTo
    {
        return $this->belongsTo(Setor::class);
    }

    public function inscricoes(): HasMany
    {
        return $this->hasMany(Inscricao::class);
    }

    public function presencas(): HasMany
    {
        return $this->hasMany(Presenca::class);
    }

    public function certificados(): HasMany
    {
        return $this->hasMany(Certificado::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function notificacoes(): HasMany
    {
        return $this->hasMany(Notificacao::class);
    }

    public function biometria(): HasOne
    {
        return $this->hasOne(Biometria::class);
    }

    public function aulasComoInstrutor(): HasMany
    {
        return $this->hasMany(Aula::class, 'instrutor_id');
    }

    public function termoAceites(): HasMany
    {
        return $this->hasMany(TermoAceite::class);
    }

    // --- RF04: termo LGPD vigente aceito? ----------------------------------------

    public function aceitouTermoVigente(): bool
    {
        $termo = Termo::vigente();

        return $termo !== null
            && $this->termoAceites()->where('termo_id', $termo->id)->exists();
    }
}
