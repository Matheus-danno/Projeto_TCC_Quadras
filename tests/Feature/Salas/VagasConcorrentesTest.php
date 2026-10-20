<?php

use App\Enums\Aprovacao;
use App\Enums\PedidoParticipacaoStatus;
use App\Livewire\Salas\Detalhe;
use App\Livewire\Salas\Pagamento;
use App\Models\PedidoParticipacao;
use App\Models\Sala;
use App\Models\User;
use Livewire\Livewire;

test('jogador com a página aberta desde quando havia vaga não estoura a capacidade ao confirmar', function () {
    $sala = Sala::factory()->create(['max_participantes' => 1]);
    $jogador = User::factory()->create();

    // O jogador abre a tela de pagamento enquanto a vaga ainda está livre...
    $tela = Livewire::actingAs($jogador)->test(Pagamento::class, ['sala' => $sala]);

    // ...e outra pessoa pega a última vaga antes de ele confirmar.
    $sala->participantes()->attach(User::factory()->create()->id);

    $tela->call('confirmarPagamento')
        ->assertSet('erro', 'Essa sala já está cheia.');

    expect($sala->participantes()->count())->toBe(1);
});

test('entrarComo conta as vagas pelo banco, não pela lista já carregada no objeto', function () {
    $sala = Sala::factory()->create(['max_participantes' => 1]);
    $sala->load('participantes');

    Sala::find($sala->id)->participantes()->attach(User::factory()->create()->id);

    $resultado = $sala->entrarComo(User::factory()->create());

    expect($resultado['sucesso'])->toBeFalse()
        ->and($resultado['mensagem'])->toBe('Essa sala já está cheia.')
        ->and($sala->participantes()->count())->toBe(1);
});

test('criador não consegue aprovar pedido quando a sala já está cheia', function () {
    $criador = User::factory()->create();
    $sala = Sala::factory()->create([
        'criador_id' => $criador->id,
        'max_participantes' => 1,
        'aprovacao' => Aprovacao::Manual,
    ]);
    $sala->participantes()->attach($criador->id);

    $pedido = PedidoParticipacao::factory()->create(['sala_id' => $sala->id]);

    Livewire::actingAs($criador)
        ->test(Detalhe::class, ['sala' => $sala])
        ->call('aprovarPedido', $pedido->id)
        ->assertSee('A sala já está cheia. Não foi possível aprovar esse pedido.');

    expect($pedido->fresh()->status)->toBe(PedidoParticipacaoStatus::Pendente)
        ->and($sala->participantes()->count())->toBe(1);
});

test('aprovar pedido com vaga coloca o jogador na sala', function () {
    $criador = User::factory()->create();
    $sala = Sala::factory()->create([
        'criador_id' => $criador->id,
        'max_participantes' => 2,
        'aprovacao' => Aprovacao::Manual,
    ]);
    $sala->participantes()->attach($criador->id);

    $pedido = PedidoParticipacao::factory()->create(['sala_id' => $sala->id]);

    expect($sala->aprovarPedido($pedido))->toBeTrue()
        ->and($pedido->fresh()->status)->toBe(PedidoParticipacaoStatus::Aprovado)
        ->and($sala->participantes()->count())->toBe(2);
});
