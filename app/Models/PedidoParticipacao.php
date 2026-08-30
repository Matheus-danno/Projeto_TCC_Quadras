<?php

namespace App\Models;

use App\Enums\PedidoParticipacaoStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PedidoParticipacao extends Model
{
    /** @use HasFactory<\Database\Factories\PedidoParticipacaoFactory> */
    use HasFactory;

    protected $fillable = [
        'sala_id',
        'user_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => PedidoParticipacaoStatus::class,
        ];
    }

    public function sala(): BelongsTo
    {
        return $this->belongsTo(Sala::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
