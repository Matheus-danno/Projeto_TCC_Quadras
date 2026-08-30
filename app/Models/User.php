<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\NivelHabilidade;
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
        'nome_estabelecimento',
        'email',
        'password',
        'role',
        'cpf',
        'cnpj',
        'data_nascimento',
        'sexo',
        'endereco',
        'cep',
        'cidade',
        'estado',
        'telefone',
        'nivel',
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
            'nivel' => NivelHabilidade::class,
            'data_nascimento' => 'date',
            'saldo_creditos' => 'decimal:2',
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
     * Pedidos feitos por este usuário na loja.
     */
    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
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

    /**
     * Conversas iniciadas por este usuário, como jogador, com donos de quadra.
     */
    public function conversas(): HasMany
    {
        return $this->hasMany(Conversa::class, 'jogador_id');
    }

    /**
     * Avaliações recebidas por este usuário como administrador de salas.
     */
    public function avaliacoesRecebidas(): HasMany
    {
        return $this->hasMany(Avaliacao::class, 'avaliado_id');
    }

    /**
     * Nota média das avaliações recebidas, arredondada a 1 casa decimal.
     */
    public function notaMedia(): ?float
    {
        $media = $this->avaliacoesRecebidas->avg('nota');

        return $media !== null ? round($media, 1) : null;
    }

    /**
     * Quantidade de salas que este usuário organizou como administrador.
     */
    public function partidasOrganizadas(): int
    {
        return $this->salasCriadas()->count();
    }

    /**
     * Ano em que o usuário se cadastrou.
     */
    public function membroDesde(): string
    {
        return $this->created_at->format('Y');
    }
}
