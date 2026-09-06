<?php

use App\Enums\PedidoStatus;
use App\Livewire\Perfil\MeusPedidos;
use App\Models\ItemPedido;
use App\Models\Pedido;
use App\Models\Produto;
use App\Models\User;
use Livewire\Livewire;

test('meus pedidos mostra apenas os pedidos do usuário autenticado, do mais recente pro mais antigo', function () {
    $user = User::factory()->create();
    $outroUsuario = User::factory()->create();
    $dono = User::factory()->donoQuadra()->create();
    $produto = Produto::factory()->create(['dono_id' => $dono->id, 'nome' => 'Bola de Futebol Society', 'preco' => 89.90]);

    $pedidoAntigo = Pedido::create([
        'user_id' => $user->id,
        'dono_id' => $dono->id,
        'status' => PedidoStatus::Aguardando,
        'total' => 89.90,
        'numero_retirada' => Pedido::gerarNumeroRetirada(),
        'comissao_percentual' => 5,
        'comissao_valor' => 4.50,
    ]);
    $pedidoAntigo->forceFill(['created_at' => now()->subDays(3)])->save();
    ItemPedido::create([
        'pedido_id' => $pedidoAntigo->id,
        'produto_id' => $produto->id,
        'quantidade' => 1,
        'preco_unitario' => 89.90,
    ]);

    $pedidoRecente = Pedido::create([
        'user_id' => $user->id,
        'dono_id' => $dono->id,
        'status' => PedidoStatus::Aguardando,
        'total' => 179.80,
        'numero_retirada' => Pedido::gerarNumeroRetirada(),
        'comissao_percentual' => 5,
        'comissao_valor' => 8.99,
    ]);
    ItemPedido::create([
        'pedido_id' => $pedidoRecente->id,
        'produto_id' => $produto->id,
        'quantidade' => 2,
        'preco_unitario' => 89.90,
    ]);

    Pedido::create([
        'user_id' => $outroUsuario->id,
        'dono_id' => $dono->id,
        'status' => PedidoStatus::Aguardando,
        'total' => 50,
        'numero_retirada' => Pedido::gerarNumeroRetirada(),
        'comissao_percentual' => 5,
        'comissao_valor' => 2.50,
    ]);

    Livewire::actingAs($user)
        ->test(MeusPedidos::class)
        ->assertSet('pedidos', fn ($pedidos) => $pedidos->pluck('id')->all() === [$pedidoRecente->id, $pedidoAntigo->id])
        ->assertSee('Bola de Futebol Society')
        ->assertSee('R$ 179,80');
});

test('perfil mostra a aba meus pedidos com estado vazio quando o usuário nunca comprou nada', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(MeusPedidos::class)
        ->assertSee('Você ainda não fez nenhum pedido');
});
