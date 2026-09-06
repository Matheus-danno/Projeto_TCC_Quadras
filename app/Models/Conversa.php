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

    /**
     * Quantidade de mensagens ainda não lidas por $userId nesta conversa
     * (enviadas pela outra pessoa e sem lida_em).
     */
    public function mensagensNaoLidasPara(int $userId): int
    {
        return $this->mensagens
            ->where('user_id', '!=', $userId)
            ->whereNull('lida_em')
            ->count();
    }

    /**
     * Marca como lidas todas as mensagens desta conversa enviadas pela
     * outra pessoa (não por $userId).
     */
    public function marcarComoLidaPara(int $userId): void
    {
        $this->mensagens()
            ->where('user_id', '!=', $userId)
            ->whereNull('lida_em')
            ->update(['lida_em' => now()]);
    }
}
