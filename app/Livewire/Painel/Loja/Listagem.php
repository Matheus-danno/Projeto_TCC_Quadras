<?php

namespace App\Livewire\Painel\Loja;

use App\Enums\PedidoStatus;
use App\Models\ItemPedido;
use App\Models\Produto;
use Flux\Concerns\InteractsWithComponents;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Listagem extends Component
{
    use InteractsWithComponents;

    public ?int $produtoParaExcluirId = null;

    public ?string $bloqueioExclusao = null;

    public string $busca = '';

    public string $filtroStatus = 'todos';

    #[Computed]
    public function produtos(): Collection
    {
        return Produto::query()
            ->where('dono_id', auth()->id())
            ->when($this->busca, fn ($query) => $query->where('nome', 'like', '%'.$this->busca.'%'))
            ->when($this->filtroStatus === 'ativo', fn ($query) => $query->where('ativo', true))
            ->when($this->filtroStatus === 'inativo', fn ($query) => $query->where('ativo', false))
            ->withSum(['itensPedido as unidades_vendidas' => function ($query) {
                $query->whereHas('pedido', fn ($q) => $q->where('status', '!=', PedidoStatus::Cancelado));
            }], 'quantidade')
            ->orderBy('nome')
            ->get();
    }

    /**
     * @return array{unidades: int, receita: float}
     */
    #[Computed]
    public function resumoVendas(): array
    {
        $itens = ItemPedido::query()
            ->whereHas('produto', fn ($query) => $query->where('dono_id', auth()->id()))
            ->whereHas('pedido', fn ($query) => $query->where('status', '!=', PedidoStatus::Cancelado))
            ->get();

        return [
            'unidades' => (int) $itens->sum('quantidade'),
            'receita' => (float) $itens->sum(fn ($item) => $item->quantidade * $item->preco_unitario),
        ];
    }

    public function ativar(int $produtoId): void
    {
        $produto = Produto::findOrFail($produtoId);

        $this->authorize('update', $produto);

        $produto->update(['ativo' => true]);

        $this->toast('Produto ativado novamente.', variant: 'success');

        unset($this->produtos);
    }

    public function desativar(int $produtoId): void
    {
        $produto = Produto::findOrFail($produtoId);

        $this->authorize('update', $produto);

        $produto->update(['ativo' => false]);

        $this->toast('Produto desativado.', variant: 'success');

        unset($this->produtos);
    }

    public function pedirExclusao(int $produtoId): void
    {
        $produto = Produto::findOrFail($produtoId);

        $this->authorize('delete', $produto);

        $this->produtoParaExcluirId = $produto->id;
        $this->bloqueioExclusao = $produto->itensPedido()->exists()
            ? 'Este produto já foi vendido e não pode ser excluído. Desative-o em vez de excluir para preservar o histórico de pedidos.'
            : null;

        $this->modal('excluir-produto')->show();
    }

    public function excluir(): void
    {
        abort_unless($this->produtoParaExcluirId, 404);

        $produto = Produto::findOrFail($this->produtoParaExcluirId);

        $this->authorize('delete', $produto);

        if ($produto->itensPedido()->exists()) {
            $this->bloqueioExclusao = 'Este produto já foi vendido e não pode ser excluído. Desative-o em vez de excluir para preservar o histórico de pedidos.';

            return;
        }

        if ($produto->imagem) {
            Storage::disk('public')->delete($produto->imagem);
        }

        $produto->delete();

        $this->modal('excluir-produto')->close();
        $this->toast('Produto excluído com sucesso.', variant: 'success');

        $this->produtoParaExcluirId = null;
        unset($this->produtos);
    }

    public function render()
    {
        return view('livewire.painel.loja.listagem');
    }
}
