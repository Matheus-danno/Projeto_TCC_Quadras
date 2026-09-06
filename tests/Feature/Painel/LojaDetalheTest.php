<?php

use App\Enums\PedidoStatus;
use App\Livewire\Painel\Loja\Detalhe;
use App\Models\ItemPedido;
use App\Models\Pedido;
use App\Models\Produto;
use App\Models\User;
use Livewire\Livewire;

function criarVendaDoProduto(Produto $produto, User $comprador, int $quantidade, PedidoStatus $status): ItemPedido
{
    $pedido = Pedido::create([
        'user_id' => $comprador->id,
        'status' => $status,
        'total' => $produto->preco * $quantidade,
    ]);

    return ItemPedido::create([
        'pedido_id' => $pedido->id,
        'produto_id' => $produto->id,
        'quantidade' => $quantidade,
        'preco_unitario' => $produto->preco,
    ]);
}

test('dono vê os detalhes e as vendas recentes do próprio produto', function () {
    $dono = User::factory()->donoQuadra()->create();
    $produto = Produto::factory()->create(['dono_id' => $dono->id, 'nome' => 'Bola de Futevôlei']);
    $comprador = User::factory()->create(['name' => 'Cliente Teste']);

    criarVendaDoProduto($produto, $comprador, 1, PedidoStatus::Aguardando);

    $this->actingAs($dono)
        ->get(route('painel.loja.show', $produto))
        ->assertOk()
        ->assertSeeLivewire(Detalhe::class)
        ->assertSee('Bola de Futevôlei')
        ->assertSee('Cliente Teste');
});

test('dono não acessa detalhes de produto de outro dono', function () {
    $dono = User::factory()->donoQuadra()->create();
    $outroDono = User::factory()->donoQuadra()->create();
    $produtoAlheio = Produto::factory()->create(['dono_id' => $outroDono->id]);

    $this->actingAs($dono)
        ->get(route('painel.loja.show', $produtoAlheio))
        ->assertForbidden();
});

test('indicadores calculam unidades vendidas e receita apenas de pedidos não cancelados', function () {
    $dono = User::factory()->donoQuadra()->create();
    $produto = Produto::factory()->create(['dono_id' => $dono->id, 'preco' => 50, 'estoque' => 30]);
    $comprador = User::factory()->create();

    criarVendaDoProduto($produto, $comprador, 2, PedidoStatus::Aguardando);
    criarVendaDoProduto($produto, $comprador, 5, PedidoStatus::Cancelado);
    criarVendaDoProduto($produto, $comprador, 1, PedidoStatus::Retirado);

    $indicadores = Livewire::actingAs($dono)
        ->test(Detalhe::class, ['produto' => $produto])
        ->instance()
        ->indicadores;

    expect($indicadores['unidadesVendidas'])->toBe(3)
        ->and($indicadores['receitaGerada'])->toEqual(150.0)
        ->and($indicadores['estoqueAtual'])->toBe(30);
});

test('vendasRecentes trazem os itens de pedido mais recentes com o comprador carregado', function () {
    $dono = User::factory()->donoQuadra()->create();
    $produto = Produto::factory()->create(['dono_id' => $dono->id]);
    $comprador = User::factory()->create(['name' => 'Comprador Recente']);

    criarVendaDoProduto($produto, $comprador, 1, PedidoStatus::Aguardando);

    $vendas = Livewire::actingAs($dono)
        ->test(Detalhe::class, ['produto' => $produto])
        ->instance()
        ->vendasRecentes;

    expect($vendas)->toHaveCount(1)
        ->and($vendas->first()->pedido->user->name)->toBe('Comprador Recente');
});

test('vendasRecentes ficam limitadas às 10 mais recentes do produto', function () {
    $dono = User::factory()->donoQuadra()->create();
    $produto = Produto::factory()->create(['dono_id' => $dono->id]);
    $comprador = User::factory()->create();

    for ($i = 0; $i < 12; $i++) {
        criarVendaDoProduto($produto, $comprador, 1, PedidoStatus::Aguardando);
    }

    $vendas = Livewire::actingAs($dono)
        ->test(Detalhe::class, ['produto' => $produto])
        ->instance()
        ->vendasRecentes;

    expect($vendas)->toHaveCount(10);
});

test('dono desativa e reativa o produto pela tela de detalhes', function () {
    $dono = User::factory()->donoQuadra()->create();
    $produto = Produto::factory()->create(['dono_id' => $dono->id, 'ativo' => true]);

    $component = Livewire::actingAs($dono)
        ->test(Detalhe::class, ['produto' => $produto])
        ->call('desativar');

    expect($produto->fresh()->ativo)->toBeFalse();

    $component->call('ativar');

    expect($produto->fresh()->ativo)->toBeTrue();
});
