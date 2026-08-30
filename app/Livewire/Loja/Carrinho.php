<?php

namespace App\Livewire\Loja;

use App\Enums\PedidoStatus;
use App\Models\Pedido;
use App\Models\Produto;
use App\Support\Carrinho as CarrinhoSessao;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Carrinho extends Component
{
    /** @var array<int, int> */
    public array $quantidades = [];

    public function mount(): void
    {
        $this->sincronizarQuantidades();
    }

    #[Computed]
    public function itens(): Collection
    {
        return CarrinhoSessao::itens();
    }

    #[Computed]
    public function total(): float
    {
        return CarrinhoSessao::total();
    }

    public function updated(string $name, mixed $value): void
    {
        if (! str_starts_with($name, 'quantidades.')) {
            return;
        }

        $produtoId = (int) str_replace('quantidades.', '', $name);

        CarrinhoSessao::atualizarQuantidade($produtoId, (int) $value);

        unset($this->itens, $this->total);
        $this->sincronizarQuantidades();
    }

    public function remover(int $produtoId): void
    {
        CarrinhoSessao::remover($produtoId);

        unset($this->itens, $this->total);
        $this->sincronizarQuantidades();
    }

    public function finalizarPedido()
    {
        abort_unless(auth()->check(), 403);

        $itens = CarrinhoSessao::itens();

        if ($itens->isEmpty()) {
            return;
        }

        $pedido = DB::transaction(function () use ($itens) {
            $pedido = Pedido::create([
                'user_id' => auth()->id(),
                'status' => PedidoStatus::Confirmado,
                'total' => CarrinhoSessao::total(),
            ]);

            foreach ($itens as $item) {
                $pedido->itens()->create([
                    'produto_id' => $item->produto->id,
                    'quantidade' => $item->quantidade,
                    'preco_unitario' => $item->produto->preco,
                ]);

                Produto::query()
                    ->where('id', $item->produto->id)
                    ->where('estoque', '>=', $item->quantidade)
                    ->decrement('estoque', $item->quantidade);
            }

            return $pedido;
        });

        CarrinhoSessao::limpar();

        return redirect()->route('loja.pedido.confirmacao', $pedido);
    }

    protected function sincronizarQuantidades(): void
    {
        $this->quantidades = CarrinhoSessao::itens()
            ->mapWithKeys(fn (object $item) => [$item->produto->id => $item->quantidade])
            ->all();
    }

    public function render()
    {
        return view('livewire.loja.carrinho');
    }
}
