<?php

namespace App\Models;

use App\Enums\PedidoStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Pedido extends Model
{
    /** @use HasFactory<\Database\Factories\PedidoFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'dono_id',
        'status',
        'total',
        'numero_retirada',
        'lote_compra',
        'comissao_percentual',
        'comissao_valor',
    ];

    protected function casts(): array
    {
        return [
            'status' => PedidoStatus::class,
            'total' => 'decimal:2',
            'comissao_percentual' => 'decimal:2',
            'comissao_valor' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Dono da loja onde o pedido deve ser retirado presencialmente.
     */
    public function dono(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dono_id');
    }

    public function itens(): HasMany
    {
        return $this->hasMany(ItemPedido::class);
    }

    /**
     * Valor que o dono efetivamente recebe, já descontada a comissão do site.
     */
    public function valorLiquido(): float
    {
        return (float) $this->total - (float) $this->comissao_valor;
    }

    /**
     * Gera um código curto e único para o cliente apresentar na retirada.
     */
    public static function gerarNumeroRetirada(): string
    {
        do {
            $codigo = strtoupper(Str::random(6));
        } while (self::where('numero_retirada', $codigo)->exists());

        return $codigo;
    }
}
