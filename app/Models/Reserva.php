<?php

namespace App\Models;

use App\Enums\ReservaStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reserva extends Model
{
    /** @use HasFactory<\Database\Factories\ReservaFactory> */
    use HasFactory;

    /**
     * Prazo mínimo, em minutos, antes do início do jogo para permitir cancelar
     * uma reserva já confirmada (mesma regra usada em Sala::sairDaSala()).
     */
    private const MINUTOS_MINIMOS_PARA_CANCELAR = 300;

    protected $fillable = [
        'quadra_id',
        'user_id',
        'cliente_nome',
        'cliente_telefone',
        'data',
        'hora_inicio',
        'hora_fim',
        'status',
        'metodo_pagamento',
        'cancelamento_tipo',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'date',
            'status' => ReservaStatus::class,
        ];
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
     * Se esta reserva pode ser cancelada agora: precisa estar pendente ou
     * confirmada, e se já estiver confirmada, faltar pelo menos 5h para o início.
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

        if ($this->status === ReservaStatus::Confirmada && $minutos < self::MINUTOS_MINIMOS_PARA_CANCELAR) {
            return false;
        }

        return true;
    }
}
