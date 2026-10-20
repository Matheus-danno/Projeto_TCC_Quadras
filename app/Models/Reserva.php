<?php

namespace App\Models;

use App\Enums\ReservaStatus;
use App\Enums\StatusPagamento;
use App\Exceptions\HorarioIndisponivelException;
use Carbon\Carbon;
use Closure;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class Reserva extends Model
{
    /** @use HasFactory<\Database\Factories\ReservaFactory> */
    use HasFactory;

    /**
     * Prazo mínimo padrão, em horas, antes do início do jogo para permitir
     * cancelar uma reserva já confirmada, usado quando o dono da quadra não
     * tem uma política de cancelamento própria definida.
     */
    private const PRAZO_CANCELAMENTO_HORAS_PADRAO = 5;

    /**
     * Código de erro do PostgreSQL para violação de exclusion constraint.
     */
    private const SQLSTATE_EXCLUSION_VIOLATION = '23P01';

    protected $fillable = [
        'quadra_id',
        'user_id',
        'cliente_nome',
        'cliente_telefone',
        'cliente_email',
        'data',
        'hora_inicio',
        'hora_fim',
        'status',
        'metodo_pagamento',
        'status_pagamento',
        'valor',
        'observacoes',
        'cancelamento_tipo',
        'motivo_cancelamento',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'date',
            'status' => ReservaStatus::class,
            'status_pagamento' => StatusPagamento::class,
            'valor' => 'decimal:2',
            'comissao_percentual' => 'decimal:2',
            'valor_total_centavos' => 'integer',
            'comissao_valor_centavos' => 'integer',
            'valor_proprietario_centavos' => 'integer',
        ];
    }

    /**
     * Ao salvar, congela o percentual e os valores de comissão (em centavos)
     * na primeira vez em que a reserva fica confirmada e paga — momento em
     * que ela passa a contar para o faturamento. Depois de calculado uma
     * vez, o snapshot nunca é recalculado, mesmo que o valor da reserva, o
     * valor/hora da quadra ou a porcentagem de comissão mudem depois.
     */
    protected static function booted(): void
    {
        static::saving(function (Reserva $reserva) {
            if ($reserva->comissao_valor_centavos !== null) {
                return;
            }

            if ($reserva->status !== ReservaStatus::Confirmada || $reserva->status_pagamento !== StatusPagamento::Pago) {
                return;
            }

            $reserva->aplicarSnapshotComissao();
        });
    }

    /**
     * Ponto único para gravar uma reserva sem sobrepor outra da mesma quadra.
     *
     * Trava a linha da quadra (lockForUpdate) dentro de uma transação, então
     * duas requisições simultâneas para a mesma quadra são atendidas uma de
     * cada vez: a segunda só verifica a disponibilidade depois que a primeira
     * já gravou. A exclusion constraint reservas_sem_sobreposicao no banco é a
     * última barreira — se ainda assim houver sobreposição, o erro do
     * PostgreSQL (SQLSTATE 23P01) vira a mesma exceção de domínio.
     *
     * @param  array<string, mixed>  $atributos  campos da reserva (data, hora_inicio, hora_fim, status...)
     * @param  (Closure(Quadra): bool)|null  $estaLivre  regra de disponibilidade de quem chama; por padrão, só checa conflito com outras reservas
     *
     * @throws HorarioIndisponivelException
     */
    public static function agendarSemConflito(Quadra $quadra, array $atributos, ?Closure $estaLivre = null): self
    {
        $estaLivre ??= fn (Quadra $quadraTravada) => ! static::conflitaCom(
            $quadraTravada->id,
            $atributos['data'],
            $atributos['hora_inicio'],
            $atributos['hora_fim'],
        );

        try {
            return DB::transaction(function () use ($quadra, $atributos, $estaLivre) {
                $quadraTravada = Quadra::whereKey($quadra->id)->lockForUpdate()->firstOrFail();

                if (! $estaLivre($quadraTravada)) {
                    throw new HorarioIndisponivelException;
                }

                return static::create(['quadra_id' => $quadraTravada->id] + $atributos);
            });
        } catch (QueryException $e) {
            if ($e->getCode() === self::SQLSTATE_EXCLUSION_VIOLATION) {
                throw new HorarioIndisponivelException($e);
            }

            throw $e;
        }
    }

    /**
     * Indica se já existe reserva ativa (não cancelada) da quadra cuja faixa de
     * horário se sobrepõe a [horaInicio, horaFim) nesta data.
     */
    public static function conflitaCom(int $quadraId, string $data, string $horaInicio, string $horaFim): bool
    {
        return static::query()
            ->where('quadra_id', $quadraId)
            ->whereDate('data', $data)
            ->where('status', '!=', ReservaStatus::Cancelada->value)
            ->where('hora_inicio', '<', $horaFim)
            ->where('hora_fim', '>', $horaInicio)
            ->exists();
    }

    public function quadra(): BelongsTo
    {
        return $this->belongsTo(Quadra::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Valor total da reserva, em reais: o lançado manualmente quando existir,
     * senão o calculado a partir do valor/hora da quadra pela duração.
     */
    public function valorEfetivoReais(): float
    {
        if ($this->valor !== null) {
            return (float) $this->valor;
        }

        $horas = (strtotime($this->hora_fim) - strtotime($this->hora_inicio)) / 3600;

        return (float) $this->quadra->valor_hora * $horas;
    }

    /**
     * Calcula e grava o snapshot de comissão (percentual vigente + valores em
     * centavos) nos atributos da reserva. Não persiste por si só — é chamado
     * a partir do evento "saving", então o save() em andamento já grava tudo.
     */
    public function aplicarSnapshotComissao(): void
    {
        $percentual = ComissaoConfiguracao::percentualAtual();
        $valorCentavos = (int) round($this->valorEfetivoReais() * 100);
        $comissaoCentavos = (int) round($valorCentavos * $percentual / 100);

        $this->comissao_percentual = $percentual;
        $this->valor_total_centavos = $valorCentavos;
        $this->comissao_valor_centavos = $comissaoCentavos;
        $this->valor_proprietario_centavos = $valorCentavos - $comissaoCentavos;
    }

    /**
     * Nome do cliente, seja de uma conta de usuário ou de um lançamento manual sem conta.
     */
    protected function nomeCliente(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->user?->name ?? $this->cliente_nome ?? '—',
        );
    }

    /**
     * Código curto de identificação da reserva, exibido na tela de confirmação.
     */
    public function codigoReserva(): string
    {
        return '#AQ-'.str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Duração da reserva formatada (ex.: "1h", "1h30min").
     */
    public function duracaoFormatada(): ?string
    {
        if (! $this->hora_inicio || ! $this->hora_fim) {
            return null;
        }

        $minutos = (strtotime($this->hora_fim) - strtotime($this->hora_inicio)) / 60;
        $horas = intdiv((int) $minutos, 60);
        $resto = (int) $minutos % 60;

        return match (true) {
            $horas > 0 && $resto > 0 => "{$horas}h{$resto}min",
            $horas > 0 => "{$horas}h",
            default => "{$resto}min",
        };
    }

    /**
     * Minutos até o início do jogo, ou null se não houver data/horário definidos
     * ou se o jogo já tiver começado.
     */
    public function minutosParaComeco(): ?int
    {
        if (! $this->data || ! $this->hora_inicio) {
            return null;
        }

        $inicio = Carbon::parse($this->data->toDateString().' '.$this->hora_inicio);

        return $inicio->isFuture() ? (int) now()->diffInMinutes($inicio) : null;
    }

    /**
     * Antecedência mínima, em horas, para cancelar esta reserva depois de
     * confirmada: a política do dono da quadra, ou o padrão da plataforma.
     */
    public function prazoCancelamentoHoras(): int
    {
        return $this->quadra?->dono?->prazo_cancelamento_horas ?? self::PRAZO_CANCELAMENTO_HORAS_PADRAO;
    }

    /**
     * Se esta reserva pode ser cancelada agora: precisa estar pendente ou
     * confirmada, e se já estiver confirmada, faltar pelo menos o prazo de
     * cancelamento (prazoCancelamentoHoras) para o início.
     */
    public function podeCancelar(): bool
    {
        if (! in_array($this->status, [ReservaStatus::Pendente, ReservaStatus::Confirmada], true)) {
            return false;
        }

        $minutos = $this->minutosParaComeco();

        if ($minutos === null) {
            return false;
        }

        if ($this->status === ReservaStatus::Confirmada && $minutos < $this->prazoCancelamentoHoras() * 60) {
            return false;
        }

        return true;
    }
}
