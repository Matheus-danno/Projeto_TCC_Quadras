<?php

namespace App\Models;

use App\Enums\AceitacaoNivel;
use App\Enums\Aprovacao;
use App\Enums\Esporte;
use App\Enums\NivelHabilidade;
use App\Enums\PedidoParticipacaoStatus;
use App\Enums\Privacidade;
use App\Enums\SalaStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sala extends Model
{
    /** @use HasFactory<\Database\Factories\SalaFactory> */
    use HasFactory;

    protected $fillable = [
        'quadra_id',
        'criador_id',
        'nome',
        'esporte',
        'max_participantes',
        'total_jogadores',
        'data',
        'horario_inicio',
        'horario_fim',
        'destaque',
        'preco_pessoa',
        'nivel_desejado',
        'aceitacao_niveis_adjacentes',
        'privacidade',
        'aprovacao',
        'status',
        'regras_adicionais',
        'reserva_id',
    ];

    /**
     * Valores padrão em memória (espelhando os defaults do banco), para que modelos
     * recém-instanciados sem esses campos explícitos (ex.: factories) já venham utilizáveis
     * sem precisar de um refresh() após o create().
     */
    protected $attributes = [
        'destaque' => false,
        'privacidade' => 'publica',
        'aprovacao' => 'automatica',
        'status' => 'aberta',
    ];

    protected function casts(): array
    {
        return [
            'esporte' => Esporte::class,
            'data' => 'date',
            'horario_inicio' => 'datetime:H:i',
            'horario_fim' => 'datetime:H:i',
            'destaque' => 'boolean',
            'preco_pessoa' => 'decimal:2',
            'nivel_desejado' => NivelHabilidade::class,
            'aceitacao_niveis_adjacentes' => AceitacaoNivel::class,
            'privacidade' => Privacidade::class,
            'aprovacao' => Aprovacao::class,
            'status' => SalaStatus::class,
        ];
    }

    public function quadra(): BelongsTo
    {
        return $this->belongsTo(Quadra::class);
    }

    public function criador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'criador_id');
    }

    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class);
    }

    public function participantes(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'participacao_salas')
            ->using(ParticipacaoSala::class)
            ->withPivot(['forma_pagamento', 'valor_pago'])
            ->withTimestamps();
    }

    public function atividades(): HasMany
    {
        return $this->hasMany(AtividadeSala::class)->latest();
    }

    public function mensagens(): HasMany
    {
        return $this->hasMany(MensagemSala::class)->oldest();
    }

    public function pedidosParticipacao(): HasMany
    {
        return $this->hasMany(PedidoParticipacao::class);
    }

    public function pedidosPendentes(): HasMany
    {
        return $this->pedidosParticipacao()->where('status', PedidoParticipacaoStatus::Pendente);
    }

    /**
     * Preço por pessoa: usa o valor definido na sala ou, na ausência dele,
     * calcula a partir do valor/hora da quadra dividido pelas vagas.
     */
    public function precoPessoaCalculado(): ?float
    {
        if ($this->preco_pessoa !== null) {
            return (float) $this->preco_pessoa;
        }

        if ($this->quadra?->valor_hora && $this->max_participantes > 0) {
            return round((float) $this->quadra->valor_hora / $this->max_participantes, 2);
        }

        return null;
    }

    /**
     * Percentual de vagas já ocupadas (0-100).
     */
    public function percentualOcupacao(): int
    {
        if ($this->max_participantes <= 0) {
            return 0;
        }

        return (int) round(($this->participantes->count() / $this->max_participantes) * 100);
    }

    /**
     * Quantidade de jogadores ainda necessários para a sala atingir 100% das vagas.
     */
    public function vagasRestantes(): int
    {
        return max(0, $this->max_participantes - $this->participantes->count());
    }

    /**
     * Minutos até o início do jogo, ou null se não houver data/horário definidos
     * ou se o jogo já tiver começado.
     */
    public function minutosParaComeco(): ?int
    {
        if (! $this->data || ! $this->horario_inicio) {
            return null;
        }

        $inicio = Carbon::parse($this->data->toDateString().' '.$this->horario_inicio->format('H:i'));

        return $inicio->isFuture() ? (int) now()->diffInMinutes($inicio) : null;
    }

    /**
     * Tempo até o início formatado de forma compacta (ex.: "28min", "3h", "2d").
     */
    public function tempoParaComecoFormatado(): ?string
    {
        $minutos = $this->minutosParaComeco();

        if ($minutos === null) {
            return null;
        }

        if ($minutos < 60) {
            return "{$minutos}min";
        }

        if ($minutos < 1440) {
            return intdiv($minutos, 60).'h';
        }

        return '1d';
    }

    /**
     * Duração da partida formatada (ex.: "1h30min"), ou null sem horário definido.
     */
    public function duracaoFormatada(): ?string
    {
        if (! $this->horario_inicio || ! $this->horario_fim) {
            return null;
        }

        $minutos = $this->horario_inicio->diffInMinutes($this->horario_fim);
        $horas = intdiv($minutos, 60);
        $resto = $minutos % 60;

        return match (true) {
            $horas > 0 && $resto > 0 => "{$horas}h{$resto}min",
            $horas > 0 => "{$horas}h00min",
            default => "{$resto}min",
        };
    }

    /**
     * Valor total da locação da quadra, quando houver valor/hora cadastrado.
     */
    public function valorTotalQuadra(): ?float
    {
        return $this->quadra?->valor_hora !== null ? (float) $this->quadra->valor_hora : null;
    }

    /**
     * Diferença entre o valor total da quadra e o que já foi arrecadado pelos
     * participantes confirmados — é o valor que o organizador paga para fechar
     * a sala mesmo sem preencher todas as vagas.
     */
    public function diferencaParaFechar(): float
    {
        $arrecadado = ($this->precoPessoaCalculado() ?? 0) * $this->participantes->count();

        return max(0, ($this->valorTotalQuadra() ?? 0) - $arrecadado);
    }

    /**
     * Se o organizador pode fechar a sala agora mesmo sem preencher todas as vagas.
     */
    public function podeFecharComVagas(): bool
    {
        return $this->status === SalaStatus::Aberta && $this->vagasRestantes() > 0;
    }

    /**
     * Fecha a sala com os jogadores atuais, após o organizador pagar a diferença.
     */
    public function fecharComMenosJogadores(): void
    {
        $totalJogadores = $this->participantes->count();

        $this->update(['status' => SalaStatus::Fechada]);

        AtividadeSala::create([
            'sala_id' => $this->id,
            'user_id' => $this->criador_id,
            'descricao' => "Sala fechada pelo administrador com {$totalJogadores}/{$this->max_participantes} jogadores",
        ]);
    }

    /**
     * Tenta adicionar o usuário como participante.
     *
     * Retorna um array com:
     * - 'sucesso' (bool): se o usuário entrou de fato na sala agora
     * - 'pendente' (bool): se um pedido de aprovação foi criado, aguardando o administrador
     * - 'mensagem' (?string): mensagem de erro, quando nem sucesso nem pendente
     */
    public function entrarComo(User $user): array
    {
        if ($this->status !== SalaStatus::Aberta) {
            return ['sucesso' => false, 'pendente' => false, 'mensagem' => 'Esta sala já está fechada.'];
        }

        if ($this->participantes->contains('id', $user->id)) {
            return ['sucesso' => false, 'pendente' => false, 'mensagem' => 'Você já está nessa sala.'];
        }

        if ($this->participantes->count() >= $this->max_participantes) {
            return ['sucesso' => false, 'pendente' => false, 'mensagem' => 'Essa sala já está cheia.'];
        }

        if ($this->aprovacao === Aprovacao::Manual) {
            $pedido = $this->pedidosParticipacao()->where('user_id', $user->id)->first();

            if ($pedido?->status === PedidoParticipacaoStatus::Pendente) {
                return ['sucesso' => false, 'pendente' => true, 'mensagem' => 'Seu pedido já está aguardando aprovação do administrador.'];
            }

            if ($pedido?->status === PedidoParticipacaoStatus::Recusado) {
                return ['sucesso' => false, 'pendente' => false, 'mensagem' => 'Seu pedido para esta sala foi recusado pelo administrador.'];
            }

            PedidoParticipacao::create([
                'sala_id' => $this->id,
                'user_id' => $user->id,
                'status' => PedidoParticipacaoStatus::Pendente,
            ]);

            AtividadeSala::create([
                'sala_id' => $this->id,
                'user_id' => $user->id,
                'descricao' => $user->name.' solicitou entrada na sala',
            ]);

            return ['sucesso' => false, 'pendente' => true, 'mensagem' => 'Pedido enviado! Aguarde a aprovação do administrador da sala.'];
        }

        $this->participantes()->attach($user->id);

        AtividadeSala::create([
            'sala_id' => $this->id,
            'user_id' => $user->id,
            'descricao' => $user->name.' entrou na sala',
        ]);

        $this->refresh();

        $jaAtingiuOitentaPorCento = AtividadeSala::query()
            ->where('sala_id', $this->id)
            ->where('descricao', 'Sala atingiu 80% de preenchimento - horário reservado!')
            ->exists();

        if (! $jaAtingiuOitentaPorCento && $this->percentualOcupacao() >= 80) {
            AtividadeSala::create([
                'sala_id' => $this->id,
                'descricao' => 'Sala atingiu 80% de preenchimento - horário reservado!',
            ]);
        }

        return ['sucesso' => true, 'pendente' => false, 'mensagem' => null];
    }

    /**
     * Aprova um pedido de participação pendente, adicionando o jogador à sala.
     */
    public function aprovarPedido(PedidoParticipacao $pedido): void
    {
        $pedido->update(['status' => PedidoParticipacaoStatus::Aprovado]);

        $this->participantes()->syncWithoutDetaching([$pedido->user_id]);

        AtividadeSala::create([
            'sala_id' => $this->id,
            'user_id' => $pedido->user_id,
            'descricao' => $pedido->user->name.' entrou na sala',
        ]);
    }

    /**
     * Recusa um pedido de participação pendente.
     */
    public function recusarPedido(PedidoParticipacao $pedido): void
    {
        $pedido->update(['status' => PedidoParticipacaoStatus::Recusado]);
    }
}
