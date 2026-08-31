<?php

use App\Livewire\Painel\Quadras\Detalhe;
use App\Models\Quadra;
use App\Models\Reserva;
use App\Models\User;
use Livewire\Livewire;

test('dono vê os detalhes e as reservas recentes da própria quadra', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id, 'nome' => 'Arena Central']);
    $cliente = User::factory()->create(['name' => 'Cliente Teste']);

    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'user_id' => $cliente->id,
    ]);

    $this->actingAs($dono)
        ->get(route('painel.quadras.show', $quadra))
        ->assertOk()
        ->assertSeeLivewire(Detalhe::class)
        ->assertSee('Arena Central')
        ->assertSee('Cliente Teste');
});

test('dono não acessa detalhes de quadra de outro dono', function () {
    $dono = User::factory()->donoQuadra()->create();
    $outroDono = User::factory()->donoQuadra()->create();
    $quadraAlheia = Quadra::factory()->create(['dono_id' => $outroDono->id]);

    $this->actingAs($dono)
        ->get(route('painel.quadras.show', $quadraAlheia))
        ->assertForbidden();
});

test('reservasRecentes ficam limitadas às 10 mais recentes da quadra', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id]);

    Reserva::factory()->count(12)->create(['quadra_id' => $quadra->id]);

    $reservas = Livewire::actingAs($dono)
        ->test(Detalhe::class, ['quadra' => $quadra])
        ->instance()
        ->reservasRecentes;

    expect($reservas)->toHaveCount(10);
});
