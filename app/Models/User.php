<?php

namespace App\Models;

use App\Enums\NivelHabilidade;
use App\Enums\Sexo;
use App\Enums\StatusCadastro;
use App\Enums\TipoPessoa;
use App\Enums\UserRole;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
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
        'telefone',
        'nivel',
        'horario_funcionamento',
        'metodos_pagamento_aceitos',
        'pausa_ativa',
        'pausa_motivo',
        'pausa_ate',
        'pausa_indeterminada',
        'prazo_cancelamento_horas',
        'chave_pix_recebimento',
        'comissao_aceita_em',
        'bloqueado_em',
        'status_cadastro',
        'titular_recebimento_nome',
        'titular_recebimento_documento',
        'checklist_aprovacao',
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
            'comissao_aceita_em' => 'datetime',
            'bloqueado_em' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'sexo' => Sexo::class,
            'nivel' => NivelHabilidade::class,
            'data_nascimento' => 'date',
            'saldo_creditos' => 'decimal:2',
            'horario_funcionamento' => 'array',
            'metodos_pagamento_aceitos' => 'array',
            'pausa_ativa' => 'boolean',
            'pausa_ate' => 'date',
            'pausa_indeterminada' => 'boolean',
            'status_cadastro' => StatusCadastro::class,
            'checklist_aprovacao' => 'array',
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
     * CNPJ formatado (00.000.000/0000-00), ou o valor cru se não tiver o
     * tamanho esperado. Usado nas telas do dono e em notas/recibos de compra.
     */
    public function cnpjFormatado(): string
    {
        if (! $this->cnpj || strlen($this->cnpj) !== 14) {
            return $this->cnpj ?? '';
        }

        return substr($this->cnpj, 0, 2).'.'.substr($this->cnpj, 2, 3).'.'.substr($this->cnpj, 5, 3).'/'.substr($this->cnpj, 8, 4).'-'.substr($this->cnpj, 12, 2);
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
     * Endereço cadastrado por este usuário (jogador ou dono de quadra).
     */
    public function endereco(): HasOne
    {
        return $this->hasOne(Endereco::class);
    }

    /**
     * Preferências de notificação deste usuário.
     */
    public function notificacoes(): HasOne
    {
        return $this->hasOne(NoticacaoUsuario::class);
    }

    /**
     * Quadras que este usuário possui como dono.
     */
    public function quadras(): HasMany
    {
        return $this->hasMany(Quadra::class, 'dono_id');
    }

    public function produtos(): HasMany
    {
        return $this->hasMany(Produto::class, 'dono_id');
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
     * Janela de funcionamento (abertura/fechamento) deste dono nesta data, considerando
     * pausa temporária, exceções cadastradas (feriados/fechamentos) e o horário semanal
     * padrão configurado em "horario_funcionamento". Retorna null quando o dono não
     * atende nessa data (pausado, exceção de fechamento total, ou dia fechado na semana).
     *
     * Donos que ainda não configuraram horário de funcionamento (campo nulo) são tratados
     * como sempre abertos das 07h às 22h, para não quebrar quadras cadastradas antes dessa
     * configuração existir.
     *
     * @return array{abertura: string, fechamento: string}|null
     */
    public function horarioFuncionamentoEm(string $data): ?array
    {
        if ($this->estaPausado()) {
            return null;
        }

        $excecao = $this->excecoesData()->whereDate('data', $data)->first();

        if ($excecao) {
            if ($excecao->fechado_dia_todo) {
                return null;
            }

            return [
                'abertura' => substr((string) $excecao->hora_abertura, 0, 5) ?: '00:00',
                'fechamento' => substr((string) $excecao->hora_fechamento, 0, 5) ?: '23:59',
            ];
        }

        if (! $this->horario_funcionamento) {
            return ['abertura' => '07:00', 'fechamento' => '22:00'];
        }

        $diaSemana = Carbon::parse($data)->dayOfWeekIso;
        $chave = match ($diaSemana) {
            6 => 'sabado',
            7 => 'domingo',
            default => 'dias_uteis',
        };

        $horario = $this->horario_funcionamento[$chave] ?? null;

        if (! $horario || ! ($horario['aberto'] ?? false)) {
            return null;
        }

        return [
            'abertura' => $horario['inicio'] ?? '00:00',
            'fechamento' => $horario['fim'] ?? '23:59',
        ];
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

    /**
     * Se esta conta está bloqueada pelo admin (não pode entrar nem permanecer logada).
     */
    public function estaBloqueado(): bool
    {
        return $this->bloqueado_em !== null;
    }

    /**
     * Documentos enviados por este usuário (identidade, titularidade de recebimento).
     */
    public function documentos(): MorphMany
    {
        return $this->morphMany(Documento::class, 'documentavel');
    }

    /**
     * Histórico de envios/reenvios e decisões do fluxo de aprovação deste cadastro.
     */
    public function historicoCadastro(): MorphMany
    {
        return $this->morphMany(CadastroHistorico::class, 'avaliavel')->latest();
    }

    /**
     * Se este proprietário está liberado para operar (quadras dele podem ser aprovadas).
     */
    public function cadastroAprovado(): bool
    {
        return $this->status_cadastro === StatusCadastro::Aprovado;
    }

    /**
     * Pessoa física (CPF) ou jurídica (CNPJ), deduzido de qual documento foi informado.
     */
    public function tipoPessoa(): ?TipoPessoa
    {
        return match (true) {
            filled($this->cnpj) => TipoPessoa::Juridica,
            filled($this->cpf) => TipoPessoa::Fisica,
            default => null,
        };
    }

    /**
     * CPF ou CNPJ deste usuário, o que estiver preenchido.
     */
    public function documentoPrincipal(): ?string
    {
        return $this->cnpj ?: $this->cpf;
    }

    /**
     * Se o nome/documento informado como titular do recebimento bate com o
     * CPF/CNPJ do próprio proprietário — checagem simples de titularidade,
     * sem integração real com banco/Pix.
     */
    public function titularidadeRecebimentoConfere(): bool
    {
        if (! $this->titular_recebimento_documento || ! $this->documentoPrincipal()) {
            return false;
        }

        $titular = preg_replace('/\D/', '', $this->titular_recebimento_documento);
        $proprio = preg_replace('/\D/', '', $this->documentoPrincipal());

        return $titular === $proprio;
    }
}
