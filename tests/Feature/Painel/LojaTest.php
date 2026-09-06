<?php

use App\Enums\PedidoStatus;
use App\Livewire\Painel\Loja\Listagem;
use App\Models\ItemPedido;
use App\Models\Pedido;
use App\Models\Produto;
use App\Models\User;
use Livewire\Livewire;

function criarVenda(Produto $produto, int $quantidade = 1, PedidoStatus $status = PedidoStatus::Aguardando): ItemPedido
{
    $pedido = Pedido::create([
        'user_id' => User::factory()->create()->id,
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

test('rota painel.loja renderiza a listagem de produtos do dono', function () {
    $dono = User::factory()->donoQuadra()->create();

    $this->actingAs($dono)
        ->get(route('painel.loja'))
        ->assertOk()
        ->assertSeeLivewire(Listagem::class);
});

test('dono vê apenas os próprios produtos na listagem', function () {
    $dono = User::factory()->donoQuadra()->create();
    $outroDono = User::factory()->donoQuadra()->create();

    Produto::factory()->create(['dono_id' => $dono->id, 'nome' => 'Produto do Dono']);
    Produto::factory()->create(['dono_id' => $outroDono->id, 'nome' => 'Produto de Outro Dono']);

    Livewire::actingAs($dono)
        ->test(Listagem::class)
        ->assertSee('Produto do Dono')
        ->assertDontSee('Produto de Outro Dono');
});

test('busca filtra a listagem pelo nome do produto', function () {
    $dono = User::factory()->donoQuadra()->create();

    Produto::factory()->create(['dono_id' => $dono->id, 'nome' => 'Bola de Futevôlei']);
    Produto::factory()->create(['dono_id' => $dono->id, 'nome' => 'Camisa Dry-Fit']);

    Livewire::actingAs($dono)
        ->test(Listagem::class)
        ->set('busca', 'Futevôlei')
        ->assertSee('Bola de Futevôlei')
        ->assertDontSee('Camisa Dry-Fit');
});

test('filtro de status mostra apenas produtos ativos ou inativos', function () {
    $dono = User::factory()->donoQuadra()->create();

    Produto::factory()->create(['dono_id' => $dono->id, 'nome' => 'Produto Ativo', 'ativo' => true]);
    Produto::factory()->create(['dono_id' => $dono->id, 'nome' => 'Produto Inativo', 'ativo' => false]);

    $component = Livewire::actingAs($dono)->test(Listagem::class);

    $component->set('filtroStatus', 'ativo')
        ->assertSee('Produto Ativo')
        ->assertDontSee('Produto Inativo');

    $component->set('filtroStatus', 'inativo')
        ->assertSee('Produto Inativo')
        ->assertDontSee('Produto Ativo');

    $component->set('filtroStatus', 'todos')
        ->assertSee('Produto Ativo')
        ->assertSee('Produto Inativo');
});

test('dono desativa e reativa um produto', function () {
    $dono = User::factory()->donoQuadra()->create();
    $produto = Produto::factory()->create(['dono_id' => $dono->id, 'ativo' => true]);

    Livewire::actingAs($dono)
        ->test(Listagem::class)
        ->call('desativar', $produto->id);

    expect($produto->fresh()->ativo)->toBeFalse();

    Livewire::actingAs($dono)
        ->test(Listagem::class)
        ->call('ativar', $produto->id);

    expect($produto->fresh()->ativo)->toBeTrue();
});

test('dono não consegue ativar nem desativar produto de outro dono', function () {
    $dono = User::factory()->donoQuadra()->create();
    $outroDono = User::factory()->donoQuadra()->create();
    $produtoAlheio = Produto::factory()->create(['dono_id' => $outroDono->id, 'ativo' => true]);

    Livewire::actingAs($dono)
        ->test(Listagem::class)
        ->call('desativar', $produtoAlheio->id)
        ->assertForbidden();

    Livewire::actingAs($dono)
        ->test(Listagem::class)
        ->call('ativar', $produtoAlheio->id)
        ->assertForbidden();

    expect($produtoAlheio->fresh()->ativo)->toBeTrue();
});

test('dono não consegue excluir produto de outro dono', function () {
    $dono = User::factory()->donoQuadra()->create();
    $outroDono = User::factory()->donoQuadra()->create();
    $produtoAlheio = Produto::factory()->create(['dono_id' => $outroDono->id]);

    Livewire::actingAs($dono)
        ->test(Listagem::class)
        ->call('pedirExclusao', $produtoAlheio->id)
        ->assertForbidden();

    expect(Produto::count())->toBe(1);
});

test('exclusão é bloqueada quando o produto já foi vendido', function () {
    $dono = User::factory()->donoQuadra()->create();
    $produto = Produto::factory()->create(['dono_id' => $dono->id]);

    criarVenda($produto);

    $component = Livewire::actingAs($dono)
        ->test(Listagem::class)
        ->call('pedirExclusao', $produto->id)
        ->call('excluir');

    expect($component->get('bloqueioExclusao'))->not->toBeNull();
    expect(Produto::query()->find($produto->id))->not->toBeNull();
});

test('exclusão funciona quando o produto nunca foi vendido', function () {
    $dono = User::factory()->donoQuadra()->create();
    $produto = Produto::factory()->create(['dono_id' => $dono->id]);

    Livewire::actingAs($dono)
        ->test(Listagem::class)
        ->call('pedirExclusao', $produto->id)
        ->call('excluir');

    expect(Produto::query()->find($produto->id))->toBeNull();
});

test('resumoVendas soma unidades e receita apenas de pedidos não cancelados dos produtos do dono', function () {
    $dono = User::factory()->donoQuadra()->create();
    $outroDono = User::factory()->donoQuadra()->create();

    $produto = Produto::factory()->create(['dono_id' => $dono->id, 'preco' => 50]);
    $produtoAlheio = Produto::factory()->create(['dono_id' => $outroDono->id, 'preco' => 100]);

    criarVenda($produto, quantidade: 2, status: PedidoStatus::Aguardando);
    criarVenda($produto, quantidade: 3, status: PedidoStatus::Cancelado);
    criarVenda($produtoAlheio, quantidade: 1, status: PedidoStatus::Aguardando);

    $resumo = Livewire::actingAs($dono)
        ->test(Listagem::class)
        ->instance()
        ->resumoVendas;

    expect($resumo['unidades'])->toBe(2)
        ->and($resumo['receita'])->toEqual(100.0);
});
