<?php

namespace App\Livewire\Painel\Loja;

use App\Enums\PedidoStatus;
use App\Models\ItemPedido;
use App\Models\Produto;
use Flux\Concerns\InteractsWithComponents;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Detalhe extends Component
{
    use InteractsWithComponents;

    public Produto $produto;

    public function mount(Produto $produto): void
    {
        $this->authorize('view', $produto);

        $this->produto = $produto;
    }

    #[Computed]
    public function indicadores(): array
    {
        $itensValidos = ItemPedido::query()
            ->where('produto_id', $this->produto->id)
            ->whereHas('pedido', fn ($query) => $query->where('status', '!=', PedidoStatus::Cancelado))
            ->get();

        return [
            'unidadesVendidas' => (int) $itensValidos->sum('quantidade'),
            'receitaGerada' => (float) $itensValidos->sum(fn (ItemPedido $item) => $item->quantidade * $item->preco_unitario),
            'estoqueAtual' => $this->produto->estoque,
        ];
    }

    #[Computed]
    public function vendasRecentes(): Collection
    {
        return ItemPedido::query()
            ->where('produto_id', $this->produto->id)
            ->with('pedido.user')
            ->latest()
            ->limit(10)
            ->get();
    }

    public function ativar(): void
    {
        $this->authorize('update', $this->produto);

        $this->produto->update(['ativo' => true]);

        $this->toast(__('Produto ativado novamente.'), variant: 'success');
    }

    public function desativar(): void
    {
        $this->authorize('update', $this->produto);

        $this->produto->update(['ativo' => false]);

        $this->toast(__('Produto desativado.'), variant: 'success');
    }

    public function render()
    {
        return view('livewire.painel.loja.detalhe');
    }
}
