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

test('participante consegue enviar mensagem privada só para o organizador', function () {
    $criador = User::factory()->create();
    $jogador = User::factory()->create();
    $sala = Sala::factory()->create(['criador_id' => $criador->id]);
    $sala->participantes()->attach([$criador->id, $jogador->id]);

    Livewire::actingAs($jogador)
        ->test(Grupo::class, ['sala' => $sala])
        ->set('novaMensagem', 'Posso levar um amigo?')
        ->set('mensagemPrivada', true)
        ->call('enviarMensagem')
        ->assertHasNoErrors();

    $mensagem = MensagemSala::first();

    expect($mensagem->destinatario_id)->toBe($criador->id);
});

test('mensagem privada aparece para quem enviou e para o organizador, mas não para outros participantes', function () {
    $criador = User::factory()->create();
    $jogador = User::factory()->create();
    $outroJogador = User::factory()->create();
    $sala = Sala::factory()->create(['criador_id' => $criador->id]);
    $sala->participantes()->attach([$criador->id, $jogador->id, $outroJogador->id]);

    MensagemSala::factory()->create([
        'sala_id' => $sala->id,
        'user_id' => $jogador->id,
        'destinatario_id' => $criador->id,
        'texto' => 'Mensagem confidencial para o organizador',
    ]);

    Livewire::actingAs($jogador)
        ->test(Grupo::class, ['sala' => $sala])
        ->assertSee('Mensagem confidencial para o organizador');

    Livewire::actingAs($criador)
        ->test(Grupo::class, ['sala' => $sala])
        ->assertSee('Mensagem confidencial para o organizador');

    Livewire::actingAs($outroJogador)
        ->test(Grupo::class, ['sala' => $sala])
        ->assertDontSee('Mensagem confidencial para o organizador');
});

test('organizador não vê a opção de enviar mensagem privada para si mesmo', function () {
    $criador = User::factory()->create();
    $sala = Sala::factory()->create(['criador_id' => $criador->id]);
    $sala->participantes()->attach($criador->id);

    Livewire::actingAs($criador)
        ->test(Grupo::class, ['sala' => $sala])
        ->assertDontSee('Enviar só para o organizador');
});

test('organizador consegue responder no privado para um participante específico', function () {
    $criador = User::factory()->create();
    $jogador = User::factory()->create(['name' => 'Ana Jogadora']);
    $outroJogador = User::factory()->create();
    $sala = Sala::factory()->create(['criador_id' => $criador->id]);
    $sala->participantes()->attach([$criador->id, $jogador->id, $outroJogador->id]);

    Livewire::actingAs($criador)
        ->test(Grupo::class, ['sala' => $sala])
        ->assertSee('Privado para Ana Jogadora')
        ->set('destinatarioId', $jogador->id)
        ->set('novaMensagem', 'Fechado, te espero lá!')
        ->call('enviarMensagem')
        ->assertHasNoErrors();

    $mensagem = MensagemSala::first();

    expect($mensagem->user_id)->toBe($criador->id)
        ->and($mensagem->destinatario_id)->toBe($jogador->id);

    Livewire::actingAs($jogador)
        ->test(Grupo::class, ['sala' => $sala])
        ->assertSee('Fechado, te espero lá!');

    Livewire::actingAs($outroJogador)
        ->test(Grupo::class, ['sala' => $sala])
        ->assertDontSee('Fechado, te espero lá!');
});

test('responder no privado preenche o destinatário com quem enviou a mensagem recebida', function () {
    $criador = User::factory()->create();
    $jogador = User::factory()->create();
    $sala = Sala::factory()->create(['criador_id' => $criador->id]);
    $sala->participantes()->attach([$criador->id, $jogador->id]);

    Livewire::actingAs($criador)
        ->test(Grupo::class, ['sala' => $sala])
        ->call('responderPrivadamente', $jogador->id)
        ->assertSet('destinatarioId', $jogador->id);
});

test('organizador não consegue mandar mensagem privada para quem não participa da sala', function () {
    $criador = User::factory()->create();
    $sala = Sala::factory()->create(['criador_id' => $criador->id]);
    $sala->participantes()->attach($criador->id);
    $forasteiro = User::factory()->create();

    Livewire::actingAs($criador)
        ->test(Grupo::class, ['sala' => $sala])
        ->set('destinatarioId', $forasteiro->id)
        ->set('novaMensagem', 'Mensagem qualquer')
        ->call('enviarMensagem')
        ->assertHasNoErrors();

    expect(MensagemSala::first()->destinatario_id)->toBeNull();
});
