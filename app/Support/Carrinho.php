<?php

namespace App\Support;

use App\Models\Produto;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

/**
 * Carrinho de compras guardado na sessão (rascunho antes do pedido).
 * Formato armazenado: [produto_id => quantidade].
 */
class Carrinho
{
    protected const CHAVE_SESSAO = 'carrinho';

    public static function adicionar(int $produtoId, int $quantidade = 1): void
    {
        $produto = Produto::findOrFail($produtoId);
        $itens = static::itensBrutos();

        $quantidadeFinal = min(($itens[$produtoId] ?? 0) + $quantidade, $produto->estoque);

        static::definirQuantidade($itens, $produtoId, $quantidadeFinal);
    }

    public static function atualizarQuantidade(int $produtoId, int $quantidade): void
    {
        $produto = Produto::findOrFail($produtoId);
        $itens = static::itensBrutos();

        static::definirQuantidade($itens, $produtoId, min($quantidade, $produto->estoque));
    }

    public static function remover(int $produtoId): void
    {
        $itens = static::itensBrutos();
        unset($itens[$produtoId]);
        static::salvar($itens);
    }

    /**
     * @return Collection<int, object{produto: Produto, quantidade: int, subtotal: float}>
     */
    public static function itens(): Collection
    {
        $itensBrutos = static::itensBrutos();

        if (empty($itensBrutos)) {
            return collect();
        }

        $produtos = Produto::whereIn('id', array_keys($itensBrutos))->get()->keyBy('id');

        return collect($itensBrutos)
            ->map(function (int $quantidade, int $produtoId) use ($produtos) {
                $produto = $produtos->get($produtoId);

                if (! $produto) {
                    return null;
                }

                return (object) [
                    'produto' => $produto,
                    'quantidade' => $quantidade,
                    'subtotal' => $produto->preco * $quantidade,
                ];
            })
            ->filter()
            ->values();
    }

    public static function total(): float
    {
        return (float) static::itens()->sum('subtotal');
    }

    public static function quantidadeTotal(): int
    {
        return array_sum(static::itensBrutos());
    }

    public static function limpar(): void
    {
        Session::forget(static::CHAVE_SESSAO);
    }

    /**
     * @return array<int, int>
     */
    protected static function itensBrutos(): array
    {
        return Session::get(static::CHAVE_SESSAO, []);
    }

    /**
     * @param  array<int, int>  $itens
     */
    protected static function definirQuantidade(array $itens, int $produtoId, int $quantidade): void
    {
        if ($quantidade <= 0) {
            unset($itens[$produtoId]);
        } else {
            $itens[$produtoId] = $quantidade;
        }

        static::salvar($itens);
    }

    /**
     * @param  array<int, int>  $itens
     */
    protected static function salvar(array $itens): void
    {
        Session::put(static::CHAVE_SESSAO, $itens);
    }
}
