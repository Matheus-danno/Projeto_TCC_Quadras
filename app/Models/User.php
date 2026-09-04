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
use Illuminate\Support\Facades\Storage;
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
        'avatar_path',
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
        'notif_confirmacao_reserva',
        'notif_lembrete_horario',
        'notif_novo_jogador_sala',
        'notif_mensagens_grupo',
        'notif_ofertas_novidades',
        'horario_funcionamento',
        'metodos_pagamento_aceitos',
        'notif_dono_dias_uteis',
        'notif_dono_cancelamento',
        'notif_dono_mensagens_clientes',
        'pausa_ativa',
        'pausa_motivo',
        'pausa_ate',
        'pausa_indeterminada',
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
            'notif_confirmacao_reserva' => 'boolean',
            'notif_lembrete_horario' => 'boolean',
            'notif_novo_jogador_sala' => 'boolean',
            'notif_mensagens_grupo' => 'boolean',
            'notif_ofertas_novidades' => 'boolean',
            'horario_funcionamento' => 'array',
            'metodos_pagamento_aceitos' => 'array',
            'notif_dono_dias_uteis' => 'boolean',
            'notif_dono_cancelamento' => 'boolean',
            'notif_dono_mensagens_clientes' => 'boolean',
            'pausa_ativa' => 'boolean',
            'pausa_ate' => 'date',
            'pausa_indeterminada' => 'boolean',
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
     * URL da foto de perfil: a enviada pelo usuário ou, na ausência dela,
     * um avatar gerado a partir do e-mail.
     */
    public function avatarUrl(): string
    {
        return $this->avatar_path
            ? Storage::disk('public')->url($this->avatar_path)
            : 'https://i.pravatar.cc/150?u='.urlencode($this->email);
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
     * Cartões de pagamento salvos por este usuário.
     */
    public function cartoes(): HasMany
    {
        return $this->hasMany(Cartao::class);
    }

    /**
     * Exceções de data (fechamentos e horários especiais) cadastradas por
     * este usuário como dono de quadra.
     */
    public function excecoesData(): HasMany
    {
        return $this->hasMany(ExcecaoData::class, 'dono_id');
    }

    /**
     * Indica se a pausa temporária das quadras deste dono está em vigor
     * agora, considerando o prazo definido em "pausa_ate" quando a pausa
     * não é por tempo indeterminado.
     */
    public function estaPausado(): bool
    {
        if (! $this->pausa_ativa) {
            return false;
        }

        if ($this->pausa_indeterminada) {
            return true;
        }

        return $this->pausa_ate !== null && $this->pausa_ate->startOfDay()->greaterThanOrEqualTo(now()->startOfDay());
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
