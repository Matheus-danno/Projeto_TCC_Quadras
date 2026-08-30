<?php

namespace App\Models;

use App\Enums\Esporte;
use App\Enums\ReservaStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quadra extends Model
{
    /** @use HasFactory<\Database\Factories\QuadraFactory> */
    use HasFactory;

    protected $fillable = [
        'dono_id',
        'nome',
        'endereco',
        'cidade',
        'cep',
        'bairro',
        'esporte',
        'valor_hora',
        'capacidade_maxima',
        'cobertura',
        'ativa',
        'descricao',
    ];

    protected function casts(): array
    {
        return [
            'esporte' => Esporte::class,
            'valor_hora' => 'decimal:2',
            'capacidade_maxima' => 'integer',
            'cobertura' => 'boolean',
            'ativa' => 'boolean',
        ];
    }

    public function dono(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dono_id');
    }

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class);
    }

    public function salas(): HasMany
    {
        return $this->hasMany(Sala::class);
    }

    public function fotos(): HasMany
    {
        return $this->hasMany(QuadraFoto::class)->orderBy('ordem');
    }

    public function fotoCapa(): ?QuadraFoto
    {
        return $this->fotos->firstWhere('capa', true) ?? $this->fotos->first();
    }

    public function temReservaFutura(): bool
    {
        return $this->reservas()
            ->whereDate('data', '>=', now()->toDateString())
            ->where('status', '!=', ReservaStatus::Cancelada->value)
            ->exists();
    }
}
