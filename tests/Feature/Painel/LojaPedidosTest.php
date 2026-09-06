<?php

use App\Enums\PedidoStatus;
use App\Livewire\Painel\Loja\Pedidos;
use App\Models\ItemPedido;
use App\Models\Pedido;
use App\Models\Produto;
use App\Models\User;
use Livewire\Livewire;

function criarPedidoDoDono(
    User $dono,
    User $comprador,
    Produto $produto,
    int $quantidade = 1,
    PedidoStatus $status = PedidoStatus::Aguardando
): Pedido {
    $total = $produto->preco * $quantidade;

    $pedido = Pedido::create([
        'user_id' => $comprador->id,
        'dono_id' => $dono->id,
        'status' => $status,
        'total' => $total,
        'numero_retirada' => Pedido::gerarNumeroRetirada(),
        'comissao_percentual' => 5,
        'comissao_valor' => round($total * 0.05, 2),
    ]);

    ItemPedido::create([
        'pedido_id' => $pedido->id,
        'produto_id' => $produto->id,
        'quantidade' => $quantidade,
        'preco_unitario' => $produto->preco,
    ]);

    return $pedido;
}

test('rota painel.loja.pedidos renderiza a listagem de pedidos do dono', function () {
    $dono = User::factory()->donoQuadra()->create();

    $this->actingAs($dono)
        ->get(route('painel.loja.pedidos'))
        ->assertOk()
        ->assertSeeLivewire(Pedidos::class);
});

test('dono vê apenas os pedidos dos próprios produtos', function () {
    $dono = User::factory()->donoQuadra()->create();
    $outroDono = User::factory()->donoQuadra()->create();
    $comprador = User::factory()->create(['name' => 'Cliente Teste']);

    $produtoDono = Produto::factory()->create(['dono_id' => $dono->id]);
    $produtoOutroDono = Produto::factory()->create(['dono_id' => $outroDono->id]);

    criarPedidoDoDono($dono, $comprador, $produtoDono);
    criarPedidoDoDono($outroDono, $comprador, $produtoOutroDono);

    $pedidos = Livewire::actingAs($dono)
        ->test(Pedidos::class)
        ->instance()
        ->pedidos;

    expect($pedidos)->toHaveCount(1)
        ->and($pedidos->first()->dono_id)->toBe($dono->id);
});

test('busca filtra pedidos por número de retirada ou nome do cliente', function () {
    $dono = User::factory()->donoQuadra()->create();
    $comprador = User::factory()->create(['name' => 'Ana Beatriz']);
    $produto = Produto::factory()->create(['dono_id' => $dono->id]);

    $pedido = criarPedidoDoDono($dono, $comprador, $produto);
    criarPedidoDoDono($dono, User::factory()->create(['name' => 'Outro Cliente']), $produto);

    Livewire::actingAs($dono)
        ->test(Pedidos::class)
        ->set('busca', $pedido->numero_retirada)
        ->assertSee($pedido->numero_retirada)
        ->assertDontSee('Outro Cliente');

    Livewire::actingAs($dono)
        ->test(Pedidos::class)
        ->set('busca', 'Ana Beatriz')
        ->assertSee('Ana Beatriz')
        ->assertDontSee('Outro Cliente');
});

test('filtro de status mostra apenas pedidos aguardando, retirados ou cancelados', function () {
    $dono = User::factory()->donoQuadra()->create();
    $comprador = User::factory()->create();
    $produto = Produto::factory()->create(['dono_id' => $dono->id]);

    criarPedidoDoDono($dono, $comprador, $produto, status: PedidoStatus::Aguardando);
    criarPedidoDoDono($dono, $comprador, $produto, status: PedidoStatus::Retirado);
    criarPedidoDoDono($dono, $comprador, $produto, status: PedidoStatus::Cancelado);

    $component = Livewire::actingAs($dono)->test(Pedidos::class);

    expect($component->set('filtroStatus', 'aguardando')->instance()->pedidos)->toHaveCount(1);
    expect($component->set('filtroStatus', 'retirado')->instance()->pedidos)->toHaveCount(1);
    expect($component->set('filtroStatus', 'cancelado')->instance()->pedidos)->toHaveCount(1);
    expect($component->set('filtroStatus', 'todos')->instance()->pedidos)->toHaveCount(3);
});

test('dono marca um pedido aguardando como retirado', function () {
    $dono = User::factory()->donoQuadra()->create();
    $comprador = User::factory()->create();
    $produto = Produto::factory()->create(['dono_id' => $dono->id]);
    $pedido = criarPedidoDoDono($dono, $comprador, $produto);

    Livewire::actingAs($dono)
        ->test(Pedidos::class)
        ->call('marcarRetirado', $pedido->id);

    expect($pedido->fresh()->status)->toBe(PedidoStatus::Retirado);
});

test('dono não consegue marcar como retirado um pedido de outro dono', function () {
    $dono = User::factory()->donoQuadra()->create();
    $outroDono = User::factory()->donoQuadra()->create();
    $comprador = User::factory()->create();
    $produto = Produto::factory()->create(['dono_id' => $outroDono->id]);
    $pedidoAlheio = criarPedidoDoDono($outroDono, $comprador, $produto);

    Livewire::actingAs($dono)
        ->test(Pedidos::class)
        ->call('marcarRetirado', $pedidoAlheio->id)
        ->assertForbidden();

    expect($pedidoAlheio->fresh()->status)->toBe(PedidoStatus::Aguardando);
});

test('não é possível marcar como retirado um pedido que já foi retirado ou cancelado', function () {
    $dono = User::factory()->donoQuadra()->create();
    $comprador = User::factory()->create();
    $produto = Produto::factory()->create(['dono_id' => $dono->id]);
    $pedidoCancelado = criarPedidoDoDono($dono, $comprador, $produto, status: PedidoStatus::Cancelado);

    Livewire::actingAs($dono)
        ->test(Pedidos::class)
        ->call('marcarRetirado', $pedidoCancelado->id)
        ->assertStatus(422);

    expect($pedidoCancelado->fresh()->status)->toBe(PedidoStatus::Cancelado);
});

test('dono cancela um pedido aguardando e o estoque do produto é devolvido', function () {
    $dono = User::factory()->donoQuadra()->create();
    $comprador = User::factory()->create();
    $produto = Produto::factory()->create(['dono_id' => $dono->id, 'estoque' => 5]);
    $pedido = criarPedidoDoDono($dono, $comprador, $produto, quantidade: 2);

    Livewire::actingAs($dono)
        ->test(Pedidos::class)
        ->call('cancelar', $pedido->id);

    expect($pedido->fresh()->status)->toBe(PedidoStatus::Cancelado)
        ->and($produto->fresh()->estoque)->toBe(7);
});

test('dono não consegue cancelar pedido de outro dono', function () {
    $dono = User::factory()->donoQuadra()->create();
    $outroDono = User::factory()->donoQuadra()->create();
    $comprador = User::factory()->create();
    $produto = Produto::factory()->create(['dono_id' => $outroDono->id, 'estoque' => 5]);
    $pedidoAlheio = criarPedidoDoDono($outroDono, $comprador, $produto, quantidade: 2);

    Livewire::actingAs($dono)
        ->test(Pedidos::class)
        ->call('cancelar', $pedidoAlheio->id)
        ->assertForbidden();

    expect($pedidoAlheio->fresh()->status)->toBe(PedidoStatus::Aguardando)
        ->and($produto->fresh()->estoque)->toBe(5);
});
