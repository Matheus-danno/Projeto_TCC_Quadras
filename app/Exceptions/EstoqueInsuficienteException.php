<?php

namespace App\Exceptions;

use App\Models\Produto;
use RuntimeException;

/**
 * Lançada ao finalizar um pedido quando o estoque de um produto não é mais
 * suficiente no momento exato do checkout (mudou entre o item entrar no
 * carrinho e o cliente confirmar — venda concorrente ou ajuste do dono).
 */
class EstoqueInsuficienteException extends RuntimeException
{
    public function __construct(public readonly Produto $produto)
    {
        parent::__construct("Estoque insuficiente para o produto #{$produto->id} ({$produto->nome}).");
    }
}
