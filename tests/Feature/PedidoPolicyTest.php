<?php

use App\Enums\PedidoStatus;
use App\Models\Pedido;
use App\Models\Quadra;
use App\Models\Reserva;
use App\Models\User;

function criarPedidoEntre(User $comprador, User $vendedor): Pedido
{
    return Pedido::create([
        'user_id' => $comprador->id,
        'dono_id' => $vendedor->id,
        'status' => PedidoStatus::Aguardando,
        'total' => 50,
    ]);
}

test('comprador vê a confirmação e a nota do próprio pedido', function () {
    $comprador = User::factory()->create();
    $pedido = criarPedidoEntre($comprador, User::factory()->donoQuadra()->create());

    $this->actingAs($comprador)->get(route('loja.pedido.confirmacao', $pedido))->assertOk();
    $this->actingAs($comprador)->get(route('loja.pedido.nota', $pedido))->assertOk();
});

test('dono que vendeu vê a nota, mas não a tela de confirmação do comprador', function () {
    $vendedor = User::factory()->donoQuadra()->create();
    $pedido = criarPedidoEntre(User::factory()->create(), $vendedor);

    $this->actingAs($vendedor)->get(route('loja.pedido.nota', $pedido))->assertOk();
    $this->actingAs($vendedor)->get(route('loja.pedido.confirmacao', $pedido))->assertForbidden();
});

test('terceiro não vê confirmação nem nota de pedido alheio', function () {
    $pedido = criarPedidoEntre(User::factory()->create(), User::factory()->donoQuadra()->create());
    $intruso = User::factory()->create();

    $this->actingAs($intruso)->get(route('loja.pedido.confirmacao', $pedido))->assertForbidden();
    $this->actingAs($intruso)->get(route('loja.pedido.nota', $pedido))->assertForbidden();
});

test('só o jogador que reservou vê a confirmação da reserva', function () {
    $jogador = User::factory()->create();
    $reserva = Reserva::factory()->create(['user_id' => $jogador->id, 'quadra_id' => Quadra::factory()]);

    $this->actingAs($jogador)->get(route('reservas.confirmacao', $reserva))->assertOk();
    $this->actingAs(User::factory()->create())->get(route('reservas.confirmacao', $reserva))->assertForbidden();
});
