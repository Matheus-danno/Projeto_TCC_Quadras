<?php

namespace App\Livewire\Painel\Loja;

use App\Enums\PedidoStatus;
use App\Models\Pedido;
use App\Models\Produto;
use Flux\Concerns\InteractsWithComponents;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Pedidos extends Component
{
    use InteractsWithComponents;

    public string $busca = '';

    public string $filtroStatus = 'todos';

    #[Computed]
    public function pedidos(): Collection
    {
        return Pedido::query()
            ->where('dono_id', auth()->id())
            ->with(['itens.produto', 'user'])
            ->when($this->busca, fn ($query) => $query->where(function ($query) {
                $query->where('numero_retirada', 'like', '%'.$this->busca.'%')
                    ->orWhereHas('user', fn ($query) => $query->where('name', 'like', '%'.$this->busca.'%'));
            }))
            ->when($this->filtroStatus !== 'todos', fn ($query) => $query->where('status', $this->filtroStatus))
            ->latest()
            ->get();
    }

    public function marcarRetirado(int $pedidoId): void
    {
        $pedido = Pedido::findOrFail($pedidoId);

        $this->authorize('update', $pedido);

        abort_unless($pedido->status === PedidoStatus::Aguardando, 422);

        $pedido->update(['status' => PedidoStatus::Retirado]);

        $this->toast('Pedido marcado como retirado.', variant: 'success');

        unset($this->pedidos);
    }

    public function cancelar(int $pedidoId): void
    {
        $pedido = Pedido::findOrFail($pedidoId);

        $this->authorize('update', $pedido);

        abort_unless($pedido->status === PedidoStatus::Aguardando, 422);

        DB::transaction(function () use ($pedido) {
            foreach ($pedido->itens as $item) {
                Produto::where('id', $item->produto_id)->increment('estoque', $item->quantidade);
            }

            $pedido->update(['status' => PedidoStatus::Cancelado]);
        });

        $this->toast('Pedido cancelado e estoque devolvido.', variant: 'success');

        unset($this->pedidos);
    }

    public function render()
    {
        return view('livewire.painel.loja.pedidos');
    }
}
