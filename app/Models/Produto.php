<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    /** @use HasFactory<\Database\Factories\ProdutoFactory> */
    use HasFactory;

    protected $fillable = [
        'nome',
        'descricao',
        'categoria',
        'preco',
        'estoque',
        'imagem',
    ];

    protected function casts(): array
    {
        return [
            'preco' => 'decimal:2',
            'estoque' => 'integer',
        ];
    }

    protected function disponivel(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->estoque > 0,
        );
    }
}
