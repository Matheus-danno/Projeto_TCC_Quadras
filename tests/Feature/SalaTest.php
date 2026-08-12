<?php

use App\Enums\Esporte;
use App\Livewire\Salas\Criar;
use App\Livewire\Salas\Listagem;
use App\Models\Sala;
use App\Models\User;
use Livewire\Livewire;

test('usuário autenticado consegue criar uma sala', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Criar::class)
        ->set('nome', 'Racha de sexta-feira')
        ->set('esporte', Esporte::Futebol->value)
        ->set('maxParticipantes', 10)
        ->call('criar')
        ->assertHasNoErrors();

    expect(Sala::count())->toBe(1);

    $sala = Sala::first();

    expect($sala->nome)->toBe('Racha de sexta-feira')
        ->and($sala->esporte)->toBe(Esporte::Futebol)
        ->and($sala->criador_id)->toBe($user->id)
        ->and($sala->max_participantes)->toBe(10)
        ->and($sala->participantes->pluck('id')->all())->toBe([$user->id]);
});

test('jogador consegue entrar em uma sala com vagas disponíveis', function () {
    $criador = User::factory()->create();
    $jogador = User::factory()->create();

    $sala = Sala::factory()->create([
        'criador_id' => $criador->id,
        'max_participantes' => 5,
    ]);

    Livewire::actingAs($jogador)
        ->test(Listagem::class)
        ->call('entrar', $sala->id)
        ->assertSet('erros', fn ($erros) => ! isset($erros[$sala->id]));

    expect($sala->participantes()->pluck('users.id')->all())->toBe([$jogador->id]);
});

test('entrada é rejeitada quando a sala está cheia', function () {
    $criador = User::factory()->create();
    $sala = Sala::factory()->create([
        'criador_id' => $criador->id,
        'max_participantes' => 1,
    ]);
    $sala->participantes()->attach($criador->id);

    $jogador = User::factory()->create();

    Livewire::actingAs($jogador)
        ->test(Listagem::class)
        ->call('entrar', $sala->id)
        ->assertSet('erros', fn ($erros) => ($erros[$sala->id] ?? null) === 'Essa sala já está cheia.');

    expect($sala->participantes()->count())->toBe(1);
});
