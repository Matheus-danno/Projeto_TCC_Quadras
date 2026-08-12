<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Sexo;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'cpf',
        'data_nascimento',
        'sexo',
        'endereco',
        'cep',
        'cidade',
        'estado',
        'telefone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

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
            'role' => UserRole::class,
            'sexo' => Sexo::class,
            'data_nascimento' => 'date',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Quadras que este usuário possui como dono.
     */
    public function quadras(): HasMany
    {
        return $this->hasMany(Quadra::class, 'dono_id');
    }

    /**
     * Reservas feitas por este usuário.
     */
    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class);
    }

    /**
     * Salas criadas por este usuário.
     */
    public function salasCriadas(): HasMany
    {
        return $this->hasMany(Sala::class, 'criador_id');
    }

    /**
     * Salas das quais este usuário participa.
     */
    public function salas(): BelongsToMany
    {
        return $this->belongsToMany(Sala::class, 'participacao_salas')
            ->using(ParticipacaoSala::class)
            ->withTimestamps();
    }
}
