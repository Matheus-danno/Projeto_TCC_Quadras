<?php

namespace App\Livewire\Loja;

use App\Enums\PedidoStatus;
use App\Models\Pedido;
use App\Models\Produto;
use App\Support\Carrinho as CarrinhoSessao;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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

    /**
     * Percentual retido pelo site em cada venda de produto.
     */
    private const COMISSAO_PERCENTUAL = 5.00;

    public function finalizarPedido()
    {
        abort_unless(auth()->check(), 403);

        $itens = CarrinhoSessao::itens();

        if ($itens->isEmpty()) {
            return;
        }

        $itensPorDono = $itens->groupBy(fn (object $item) => $item->produto->dono_id);
        $loteCompra = (string) Str::uuid();

        $pedidos = DB::transaction(function () use ($itensPorDono, $loteCompra) {
            return $itensPorDono->map(function (Collection $itensDoDono) use ($loteCompra) {
                $total = (float) $itensDoDono->sum('subtotal');
                $comissaoValor = round($total * self::COMISSAO_PERCENTUAL / 100, 2);

                $pedido = Pedido::create([
                    'user_id' => auth()->id(),
                    'dono_id' => $itensDoDono->first()->produto->dono_id,
                    'status' => PedidoStatus::Aguardando,
                    'total' => $total,
                    'numero_retirada' => Pedido::gerarNumeroRetirada(),
                    'lote_compra' => $loteCompra,
                    'comissao_percentual' => self::COMISSAO_PERCENTUAL,
                    'comissao_valor' => $comissaoValor,
                ]);

                foreach ($itensDoDono as $item) {
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
            })->values();
        });

        CarrinhoSessao::limpar();

        return redirect()->route('loja.pedido.confirmacao', $pedidos->first());
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
