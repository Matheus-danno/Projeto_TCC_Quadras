<?php

namespace App\Models;

use App\Enums\Esporte;
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
        'bairro',
        'esporte',
        'valor_hora',
        'cobertura',
        'descricao',
    ];

    protected function casts(): array
    {
        return [
            'esporte' => Esporte::class,
            'valor_hora' => 'decimal:2',
            'cobertura' => 'boolean',
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
}
