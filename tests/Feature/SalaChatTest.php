<?php

use App\Livewire\Salas\Grupo;
use App\Models\MensagemSala;
use App\Models\Sala;
use App\Models\User;
use Livewire\Livewire;

test('participante consegue enviar uma mensagem no chat da sala', function () {
    $criador = User::factory()->create();
    $jogador = User::factory()->create();
    $sala = Sala::factory()->create(['criador_id' => $criador->id]);
    $sala->participantes()->attach([$criador->id, $jogador->id]);

    Livewire::actingAs($jogador)
        ->test(Grupo::class, ['sala' => $sala])
        ->set('novaMensagem', 'Alguém confirma presença?')
        ->call('enviarMensagem')
        ->assertHasNoErrors()
        ->assertSet('novaMensagem', '')
        ->assertSee('Alguém confirma presença?');

    expect(MensagemSala::count())->toBe(1);

    $mensagem = MensagemSala::first();

    expect($mensagem->sala_id)->toBe($sala->id)
        ->and($mensagem->user_id)->toBe($jogador->id)
        ->and($mensagem->texto)->toBe('Alguém confirma presença?');
});

test('mensagem vazia é rejeitada', function () {
    $jogador = User::factory()->create();
    $sala = Sala::factory()->create();
    $sala->participantes()->attach($jogador->id);

    Livewire::actingAs($jogador)
        ->test(Grupo::class, ['sala' => $sala])
        ->set('novaMensagem', '')
        ->call('enviarMensagem')
        ->assertHasErrors('novaMensagem');

    expect(MensagemSala::count())->toBe(0);
});

test('mensagens aparecem em ordem cronológica para todos os participantes, incluindo o organizador', function () {
    $criador = User::factory()->create(['name' => 'Carlos Organizador']);
    $jogador = User::factory()->create(['name' => 'Ana Jogadora']);
    $sala = Sala::factory()->create(['criador_id' => $criador->id]);
    $sala->participantes()->attach([$criador->id, $jogador->id]);

    MensagemSala::factory()->create([
        'sala_id' => $sala->id,
        'user_id' => $criador->id,
        'texto' => 'Bem-vindos à partida!',
        'created_at' => now()->subMinute(),
    ]);
    MensagemSala::factory()->create([
        'sala_id' => $sala->id,
        'user_id' => $jogador->id,
        'texto' => 'Vou chegar um pouco mais cedo',
        'created_at' => now(),
    ]);

    Livewire::actingAs($jogador)
        ->test(Grupo::class, ['sala' => $sala])
        ->assertSeeInOrder(['Bem-vindos à partida!', 'Vou chegar um pouco mais cedo'])
        ->assertSee('Organizador');
});

test('usuário que não participa da sala não consegue ver nem enviar mensagens', function () {
    $sala = Sala::factory()->create();
    $forasteiro = User::factory()->create();

    Livewire::actingAs($forasteiro)
        ->test(Grupo::class, ['sala' => $sala])
        ->assertForbidden();

    $this->actingAs($forasteiro)
        ->get(route('salas.grupo', $sala))
        ->assertForbidden();
});
