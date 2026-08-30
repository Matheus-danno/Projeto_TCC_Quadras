<?php

namespace App\Models;

use App\Enums\AceitacaoNivel;
use App\Enums\Esporte;
use App\Enums\NivelHabilidade;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        'data',
        'hora_inicio',
        'duracao_minutos',
        'quantidade_horas',
        'nivel_desejado',
        'aceitacao_niveis_adjacentes',
        'privada',
        'aprovacao_manual',
        'regras_adicionais',
        'reserva_id',
    ];

    protected function casts(): array
    {
        return [
            'esporte' => Esporte::class,
            'data' => 'date',
            'nivel_desejado' => NivelHabilidade::class,
            'aceitacao_niveis_adjacentes' => AceitacaoNivel::class,
            'privada' => 'boolean',
            'aprovacao_manual' => 'boolean',
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

    public function valorPorPessoa(): float
    {
        if (! $this->quadra) {
            return 0.0;
        }

        $horas = max($this->duracao_minutos, 1) / 60;

        return round(($this->quadra->valor_hora * $horas) / max($this->max_participantes, 1), 2);
    }

    protected function horaFim(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->hora_inicio || ! $this->duracao_minutos) {
                    return null;
                }

                return Carbon::parse($this->hora_inicio)
                    ->addMinutes($this->duracao_minutos)
                    ->format('H:i:s');
            },
        );
    }
}
