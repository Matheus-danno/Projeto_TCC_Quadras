<?php

namespace App\Livewire\Loja;

use App\Enums\PedidoStatus;
use App\Exceptions\EstoqueInsuficienteException;
use App\Models\ComissaoConfiguracao;
use App\Models\Pedido;
use App\Models\Produto;
use App\Notifications\NovoPedidoRecebido;
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

    /**
     * A loja usa o layout bootstrap (não o layout Flux do painel/admin), que
     * não tem o componente <flux:toast> montado — então $this->toast() não
     * teria onde aparecer. Por isso o erro vira uma prop simples, exibida
     * como alerta Bootstrap na própria view.
     */
    public ?string $erroEstoque = null;

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

        $this->erroEstoque = null;

        $itens = CarrinhoSessao::itens();

        if ($itens->isEmpty()) {
            return;
        }

        $itensPorDono = $itens->groupBy(fn (object $item) => $item->produto->dono_id);
        $loteCompra = (string) Str::uuid();
        $comissaoPercentual = ComissaoConfiguracao::percentualAtual();

        try {
            $pedidos = DB::transaction(function () use ($itensPorDono, $loteCompra, $comissaoPercentual) {
                return $itensPorDono->map(function (Collection $itensDoDono) use ($loteCompra, $comissaoPercentual) {
                    $total = (float) $itensDoDono->sum('subtotal');
                    $comissaoValor = round($total * $comissaoPercentual / 100, 2);

                    $pedido = Pedido::create([
                        'user_id' => auth()->id(),
                        'dono_id' => $itensDoDono->first()->produto->dono_id,
                        'status' => PedidoStatus::Aguardando,
                        'total' => $total,
                        'numero_retirada' => Pedido::gerarNumeroRetirada(),
                        'lote_compra' => $loteCompra,
                        'comissao_percentual' => $comissaoPercentual,
                        'comissao_valor' => $comissaoValor,
                    ]);

                    foreach ($itensDoDono as $item) {
                        // Reconfirma o estoque atomicamente: se mudou desde que o item
                        // entrou no carrinho (venda concorrente, ajuste do dono), a
                        // cláusula where não bate e 0 linhas são afetadas — sem isso o
                        // pedido seria cobrado do cliente mesmo sem estoque de verdade.
                        $linhasAfetadas = Produto::query()
                            ->where('id', $item->produto->id)
                            ->where('estoque', '>=', $item->quantidade)
                            ->decrement('estoque', $item->quantidade);

                        if ($linhasAfetadas === 0) {
                            throw new EstoqueInsuficienteException($item->produto);
                        }

                        $pedido->itens()->create([
                            'produto_id' => $item->produto->id,
                            'quantidade' => $item->quantidade,
                            'preco_unitario' => $item->produto->preco,
                        ]);
                    }

                    return $pedido;
                })->values();
            });
        } catch (EstoqueInsuficienteException $e) {
            unset($this->itens, $this->total);
            $this->sincronizarQuantidades();

            $this->erroEstoque = __('O estoque de ":produto" mudou e não é mais suficiente. Ajuste a quantidade no carrinho.', ['produto' => $e->produto->nome]);

            return;
        }

        // Notificações só depois do commit: dentro da transação, se um dono
        // seguinte estourasse o estoque, o rollback desfaria o pedido mas a
        // notificação síncrona já teria sido enviada.
        $pedidos->each(fn (Pedido $pedido) => $pedido->dono->notify(new NovoPedidoRecebido($pedido)));

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
