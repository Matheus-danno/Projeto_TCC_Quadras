<?php

use App\Enums\Aprovacao;
use App\Livewire\Salas\Detalhe;
use App\Livewire\Salas\PagarDiferenca;
use App\Models\PedidoParticipacao;
use App\Models\Sala;
use App\Models\User;
use App\Policies\SalaPolicy;
use Livewire\Livewire;

test('criador pode gerenciar a própria sala', function () {
    $criador = User::factory()->create();
    $sala = Sala::factory()->create(['criador_id' => $criador->id]);

    expect((new SalaPolicy)->gerenciar($criador, $sala))->toBeTrue();
});

test('outro jogador não pode gerenciar a sala', function () {
    $sala = Sala::factory()->create();

    expect((new SalaPolicy)->gerenciar(User::factory()->create(), $sala))->toBeFalse();
});

test('terceiro não consegue renomear a sala nem aprovar pedidos', function () {
    $sala = Sala::factory()->create(['aprovacao' => Aprovacao::Manual]);
    $pedido = PedidoParticipacao::factory()->create(['sala_id' => $sala->id]);
    $intruso = User::factory()->create();

    Livewire::actingAs($intruso)
        ->test(Detalhe::class, ['sala' => $sala])
        ->call('iniciarEdicaoNome')
        ->assertForbidden();

    Livewire::actingAs($intruso)
        ->test(Detalhe::class, ['sala' => $sala])
        ->call('aprovarPedido', $pedido->id)
        ->assertForbidden();
});

test('só o criador abre a tela de pagar a diferença', function () {
    $criador = User::factory()->create();
    $sala = Sala::factory()->create(['criador_id' => $criador->id, 'max_participantes' => 5]);
    $sala->participantes()->attach($criador->id);

    Livewire::actingAs($criador)
        ->test(PagarDiferenca::class, ['sala' => $sala])
        ->assertOk();

    Livewire::actingAs(User::factory()->create())
        ->test(PagarDiferenca::class, ['sala' => $sala])
        ->assertForbidden();
});
