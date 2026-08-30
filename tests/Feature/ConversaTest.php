<?php

use App\Livewire\Painel\Mensagens as PainelMensagens;
use App\Livewire\Perfil\Mensagens as PerfilMensagens;
use App\Models\Conversa;
use App\Models\MensagemConversa;
use App\Models\Quadra;
use App\Models\Sala;
use App\Models\User;
use Livewire\Livewire;

test('aba de mensagens só aparece no perfil para quem já organizou uma sala', function () {
    $organizador = User::factory()->create();
    Sala::factory()->create(['criador_id' => $organizador->id]);

    $semSala = User::factory()->create();

    $this->actingAs($organizador)->get(route('perfil'))->assertSee('Mensagens');
    $this->actingAs($semSala)->get(route('perfil'))->assertDontSee('Mensagens');
});

test('jogador que organizou uma sala vê a quadra alugada e consegue enviar a primeira mensagem', function () {
    $jogador = User::factory()->create();
    $dono = User::factory()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id, 'nome' => 'Arena Teste']);
    Sala::factory()->create(['criador_id' => $jogador->id, 'quadra_id' => $quadra->id]);

    Livewire::actingAs($jogador)
        ->test(PerfilMensagens::class)
        ->assertSee('Arena Teste')
        ->call('selecionarQuadra', $quadra->id)
        ->set('novaMensagem', 'O estacionamento é gratuito?')
        ->call('enviarMensagem')
        ->assertHasNoErrors()
        ->assertSee('O estacionamento é gratuito?');

    expect(Conversa::count())->toBe(1);

    $conversa = Conversa::first();

    expect($conversa->quadra_id)->toBe($quadra->id)
        ->and($conversa->jogador_id)->toBe($jogador->id);

    $mensagem = MensagemConversa::first();

    expect($mensagem->conversa_id)->toBe($conversa->id)
        ->and($mensagem->user_id)->toBe($jogador->id)
        ->and($mensagem->texto)->toBe('O estacionamento é gratuito?');
});

test('jogador não consegue mandar mensagem sobre uma quadra que não alugou', function () {
    $jogador = User::factory()->create();
    $quadra = Quadra::factory()->create();

    Livewire::actingAs($jogador)
        ->test(PerfilMensagens::class)
        ->set('novaMensagem', 'Oi')
        ->call('selecionarQuadra', $quadra->id)
        ->assertForbidden();

    expect(Conversa::count())->toBe(0);
});

test('dono da quadra vê a conversa e consegue responder', function () {
    $jogador = User::factory()->create(['name' => 'Jogador Teste']);
    $dono = User::factory()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id]);
    $conversa = Conversa::factory()->create(['quadra_id' => $quadra->id, 'jogador_id' => $jogador->id]);
    MensagemConversa::factory()->create(['conversa_id' => $conversa->id, 'user_id' => $jogador->id, 'texto' => 'Mensagem inicial']);

    Livewire::actingAs($dono)
        ->test(PainelMensagens::class)
        ->assertSee('Jogador Teste')
        ->call('selecionarConversa', $conversa->id)
        ->assertSee('Mensagem inicial')
        ->set('novaMensagem', 'Pode sim!')
        ->call('enviarMensagem')
        ->assertHasNoErrors()
        ->assertSee('Pode sim!');

    $resposta = MensagemConversa::latest('id')->first();

    expect($resposta->user_id)->toBe($dono->id)
        ->and($resposta->conversa_id)->toBe($conversa->id);
});

test('dono de outra quadra não consegue ver nem responder a conversa', function () {
    $jogador = User::factory()->create();
    $dono = User::factory()->create();
    $outroDono = User::factory()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id]);
    $conversa = Conversa::factory()->create(['quadra_id' => $quadra->id, 'jogador_id' => $jogador->id]);

    expect(fn () => Livewire::actingAs($outroDono)
        ->test(PainelMensagens::class)
        ->call('selecionarConversa', $conversa->id)
    )->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
});

test('mensagens em uma mesma quadra reaproveitam a conversa já existente', function () {
    $jogador = User::factory()->create();
    $quadra = Quadra::factory()->create();
    Sala::factory()->create(['criador_id' => $jogador->id, 'quadra_id' => $quadra->id]);

    $component = Livewire::actingAs($jogador)->test(PerfilMensagens::class);

    $component->call('selecionarQuadra', $quadra->id)
        ->set('novaMensagem', 'Primeira mensagem')
        ->call('enviarMensagem');

    $component->call('selecionarQuadra', $quadra->id)
        ->set('novaMensagem', 'Segunda mensagem')
        ->call('enviarMensagem');

    expect(Conversa::count())->toBe(1)
        ->and(MensagemConversa::count())->toBe(2);
});
