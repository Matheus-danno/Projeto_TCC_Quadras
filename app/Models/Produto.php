<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Produto extends Model
{
    /** @use HasFactory<\Database\Factories\ProdutoFactory> */
    use HasFactory;

    protected $fillable = [
        'dono_id',
        'nome',
        'descricao',
        'categoria',
        'preco',
        'estoque',
        'ativo',
        'imagem',
    ];

    protected function casts(): array
    {
        return [
            'preco' => 'decimal:2',
            'estoque' => 'integer',
            'ativo' => 'boolean',
        ];
    }

    protected function disponivel(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->ativo && $this->estoque > 0,
        );
    }

    public function dono(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dono_id');
    }

    public function itensPedido(): HasMany
    {
        return $this->hasMany(ItemPedido::class);
    }

    /**
     * URL pública da imagem do produto, ou null se ele não tiver uma.
     */
    public function imagemUrl(): ?string
    {
        return $this->imagem ? Storage::disk('public')->url($this->imagem) : null;
    }
}
