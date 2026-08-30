<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversa extends Model
{
    /** @use HasFactory<\Database\Factories\ConversaFactory> */
    use HasFactory;

    protected $fillable = [
        'quadra_id',
        'jogador_id',
    ];

    public function quadra(): BelongsTo
    {
        return $this->belongsTo(Quadra::class);
    }

    public function jogador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'jogador_id');
    }

    public function mensagens(): HasMany
    {
        return $this->hasMany(MensagemConversa::class)->oldest();
    }

    /**
     * Dono da quadra alugada, com quem o jogador está conversando.
     */
    public function dono(): ?User
    {
        return $this->quadra->dono;
    }

    public function ultimaMensagem(): ?MensagemConversa
    {
        return $this->mensagens->last();
    }
}
