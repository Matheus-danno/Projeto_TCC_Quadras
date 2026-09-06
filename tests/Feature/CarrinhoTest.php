<?php

use App\Enums\PedidoStatus;
use App\Livewire\Loja\Carrinho as CarrinhoComponent;
use App\Livewire\Loja\Detalhe;
use App\Models\Pedido;
use App\Models\Produto;
use App\Models\User;
use App\Support\Carrinho;
use Livewire\Livewire;

test('visitante não autenticado vê o link de login ao tentar adicionar ao carrinho', function () {
    $produto = Produto::factory()->create(['estoque' => 10]);

    Livewire::test(Detalhe::class, ['produto' => $produto])
        ->call('adicionarAoCarrinho')
        ->assertSet('precisaLogin', true)
        ->assertSee('entrar');

    expect(Carrinho::quantidadeTotal())->toBe(0);
});

test('usuário autenticado consegue adicionar um produto ao carrinho', function () {
    $user = User::factory()->create();
    $produto = Produto::factory()->create(['estoque' => 10]);

    Livewire::actingAs($user)
        ->test(Detalhe::class, ['produto' => $produto])
        ->set('quantidade', 2)
        ->call('adicionarAoCarrinho')
        ->assertSet('mensagemSucesso', fn ($mensagem) => str_contains($mensagem, $produto->nome));

    expect(Carrinho::quantidadeTotal())->toBe(2)
        ->and(Carrinho::total())->toBe((float) $produto->preco * 2);
});

test('adicionar mais unidades do que o estoque disponível é limitado ao estoque', function () {
    $user = User::factory()->create();
    $produto = Produto::factory()->create(['estoque' => 3]);

    Livewire::actingAs($user)->test(Detalhe::class, ['produto' => $produto])
        ->set('quantidade', 10)
        ->call('adicionarAoCarrinho');

    expect(Carrinho::quantidadeTotal())->toBe(3);

    Carrinho::adicionar($produto->id, 5);

    expect(Carrinho::quantidadeTotal())->toBe(3);
});

test('página do carrinho mostra os itens adicionados e permite atualizar a quantidade', function () {
    $user = User::factory()->create();
    $produto = Produto::factory()->create(['estoque' => 10, 'preco' => 50]);

    actingAsComUmItemNoCarrinho($user, $produto, 1);

    Livewire::actingAs($user)
        ->test(CarrinhoComponent::class)
        ->assertSee($produto->nome)
        ->set("quantidades.{$produto->id}", 3)
        ->assertSet('total', 150.0);

    expect(Carrinho::itens()->firstWhere('produto.id', $produto->id)->quantidade)->toBe(3);
});

test('remover um item esvazia o carrinho', function () {
    $user = User::factory()->create();
    $produto = Produto::factory()->create(['estoque' => 10]);

    actingAsComUmItemNoCarrinho($user, $produto, 1);

    Livewire::actingAs($user)
        ->test(CarrinhoComponent::class)
        ->call('remover', $produto->id)
        ->assertSee('Seu carrinho está vazio');

    expect(Carrinho::itens())->toHaveCount(0);
});

test('finalizar pedido cria o pedido, decrementa o estoque e limpa o carrinho', function () {
    $user = User::factory()->create();
    $dono = User::factory()->donoQuadra()->create();
    $produtoA = Produto::factory()->create(['dono_id' => $dono->id, 'estoque' => 10, 'preco' => 89.90]);
    $produtoB = Produto::factory()->create(['dono_id' => $dono->id, 'estoque' => 5, 'preco' => 49.90]);

    actingAsComUmItemNoCarrinho($user, $produtoA, 2);
    actingAsComUmItemNoCarrinho($user, $produtoB, 1);

    $totalEsperado = $produtoA->preco * 2 + $produtoB->preco * 1;

    $redirect = Livewire::actingAs($user)
        ->test(CarrinhoComponent::class)
        ->call('finalizarPedido')
        ->assertRedirect();

    expect(Pedido::count())->toBe(1);

    $pedido = Pedido::first();

    expect($pedido->user_id)->toBe($user->id)
        ->and($pedido->dono_id)->toBe($dono->id)
        ->and($pedido->status)->toBe(PedidoStatus::Aguardando)
        ->and($pedido->numero_retirada)->not->toBeNull()
        ->and((float) $pedido->comissao_percentual)->toBe(5.0)
        ->and(round((float) $pedido->comissao_valor, 2))->toBe(round($totalEsperado * 0.05, 2))
        ->and(round((float) $pedido->total, 2))->toBe(round((float) $totalEsperado, 2))
        ->and($pedido->itens)->toHaveCount(2);

    $redirect->assertRedirect(route('loja.pedido.confirmacao', $pedido));

    expect($produtoA->fresh()->estoque)->toBe(8)
        ->and($produtoB->fresh()->estoque)->toBe(4)
        ->and(Carrinho::itens())->toHaveCount(0);
});

test('carrinho com produtos de donos diferentes gera um pedido separado por dono', function () {
    $user = User::factory()->create();
    $donoA = User::factory()->donoQuadra()->create();
    $donoB = User::factory()->donoQuadra()->create();
    $produtoA = Produto::factory()->create(['dono_id' => $donoA->id, 'estoque' => 10, 'preco' => 100]);
    $produtoB = Produto::factory()->create(['dono_id' => $donoB->id, 'estoque' => 10, 'preco' => 50]);

    actingAsComUmItemNoCarrinho($user, $produtoA, 1);
    actingAsComUmItemNoCarrinho($user, $produtoB, 2);

    Livewire::actingAs($user)
        ->test(CarrinhoComponent::class)
        ->call('finalizarPedido')
        ->assertRedirect();

    expect(Pedido::count())->toBe(2);

    $pedidoA = Pedido::where('dono_id', $donoA->id)->first();
    $pedidoB = Pedido::where('dono_id', $donoB->id)->first();

    expect($pedidoA->total)->toEqual(100.0)
        ->and($pedidoA->comissao_valor)->toEqual(5.0)
        ->and($pedidoA->itens)->toHaveCount(1)
        ->and($pedidoB->total)->toEqual(100.0)
        ->and($pedidoB->comissao_valor)->toEqual(5.0)
        ->and($pedidoB->itens)->toHaveCount(1)
        ->and($pedidoA->numero_retirada)->not->toBe($pedidoB->numero_retirada)
        ->and($pedidoA->lote_compra)->not->toBeNull()
        ->and($pedidoA->lote_compra)->toBe($pedidoB->lote_compra);

    expect($produtoA->fresh()->estoque)->toBe(9)
        ->and($produtoB->fresh()->estoque)->toBe(8);
});

test('preço do item do pedido fica congelado mesmo se o preço do produto mudar depois', function () {
    $user = User::factory()->create();
    $produto = Produto::factory()->create(['estoque' => 10, 'preco' => 100]);

    actingAsComUmItemNoCarrinho($user, $produto, 1);

    Livewire::actingAs($user)->test(CarrinhoComponent::class)->call('finalizarPedido');

    $produto->update(['preco' => 999]);

    $item = Pedido::first()->itens->first();

    expect((float) $item->preco_unitario)->toBe(100.0);
});

test('finalizar pedido com carrinho vazio não cria pedido', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)->test(CarrinhoComponent::class)->call('finalizarPedido');

    expect(Pedido::count())->toBe(0);
});

test('só o dono do pedido pode ver a página de confirmação', function () {
    $dono = User::factory()->create();
    $outroUsuario = User::factory()->create();
    $produto = Produto::factory()->create(['estoque' => 10]);

    actingAsComUmItemNoCarrinho($dono, $produto, 1);
    Livewire::actingAs($dono)->test(CarrinhoComponent::class)->call('finalizarPedido');

    $pedido = Pedido::first();

    $this->actingAs($dono)
        ->get(route('loja.pedido.confirmacao', $pedido))
        ->assertOk()
        ->assertSee($produto->nome);

    $this->actingAs($outroUsuario)
        ->get(route('loja.pedido.confirmacao', $pedido))
        ->assertForbidden();
});

function actingAsComUmItemNoCarrinho(User $user, Produto $produto, int $quantidade): void
{
    Livewire::actingAs($user)
        ->test(Detalhe::class, ['produto' => $produto])
        ->set('quantidade', $quantidade)
        ->call('adicionarAoCarrinho');
}
