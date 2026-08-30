<?php

namespace App\Models;

use App\Enums\ReservaStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reserva extends Model
{
    /** @use HasFactory<\Database\Factories\ReservaFactory> */
    use HasFactory;

    protected $fillable = [
        'quadra_id',
        'user_id',
        'cliente_nome',
        'cliente_telefone',
        'data',
        'hora_inicio',
        'hora_fim',
        'status',
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
}
