<?php

use App\Enums\PedidoStatus;
use App\Livewire\Painel\Mensagens as PainelMensagens;
use App\Livewire\Perfil\Mensagens as PerfilMensagens;
use App\Models\Conversa;
use App\Models\ItemPedido;
use App\Models\MensagemConversa;
use App\Models\Pedido;
use App\Models\Produto;
use App\Models\Quadra;
use App\Models\Sala;
use App\Models\User;
use Livewire\Livewire;

function criarPedidoComItem(User $dono, User $comprador, ?Produto $produto = null): Pedido
{
    $produto ??= Produto::factory()->create(['dono_id' => $dono->id]);

    $pedido = Pedido::create([
        'user_id' => $comprador->id,
        'dono_id' => $dono->id,
        'status' => PedidoStatus::Aguardando,
        'total' => $produto->preco,
        'numero_retirada' => Pedido::gerarNumeroRetirada(),
        'comissao_percentual' => 5,
        'comissao_valor' => round($produto->preco * 0.05, 2),
    ]);

    ItemPedido::create([
        'pedido_id' => $pedido->id,
        'produto_id' => $produto->id,
        'quantidade' => 1,
        'preco_unitario' => $produto->preco,
    ]);

    return $pedido;
}

test('aba de mensagens só aparece no perfil para quem já organizou uma sala', function () {
    $organizador = User::factory()->create();
    Sala::factory()->create(['criador_id' => $organizador->id]);

    $semSala = User::factory()->create();

    $this->actingAs($organizador)->get(route('perfil'))->assertSee('id="mensagens-tab"', false);
    $this->actingAs($semSala)->get(route('perfil'))->assertDontSee('id="mensagens-tab"', false);
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

test('aba de mensagens aparece no perfil para quem comprou na loja, mesmo sem ter criado sala', function () {
    $dono = User::factory()->donoQuadra()->create();
    $comprador = User::factory()->create();
    criarPedidoComItem($dono, $comprador);

    $this->actingAs($comprador)->get(route('perfil'))->assertSee('id="mensagens-tab"', false);
});

test('jogador que comprou um produto vê o pedido e consegue falar com o vendedor', function () {
    $dono = User::factory()->donoQuadra()->create();
    $comprador = User::factory()->create();
    $produto = Produto::factory()->create(['dono_id' => $dono->id, 'nome' => 'Raquete Teste']);
    $pedido = criarPedidoComItem($dono, $comprador, $produto);

    Livewire::actingAs($comprador)
        ->test(PerfilMensagens::class)
        ->assertSee('Raquete Teste')
        ->call('selecionarPedido', $pedido->id)
        ->set('novaMensagem', 'A raquete tem garantia?')
        ->call('enviarMensagem')
        ->assertHasNoErrors()
        ->assertSee('A raquete tem garantia?');

    expect(Conversa::count())->toBe(1);

    $conversa = Conversa::first();

    expect($conversa->pedido_id)->toBe($pedido->id)
        ->and($conversa->quadra_id)->toBeNull()
        ->and($conversa->jogador_id)->toBe($comprador->id);

    $mensagem = MensagemConversa::first();

    expect($mensagem->conversa_id)->toBe($conversa->id)
        ->and($mensagem->user_id)->toBe($comprador->id)
        ->and($mensagem->texto)->toBe('A raquete tem garantia?');
});

test('jogador não consegue mandar mensagem sobre um pedido que não é seu', function () {
    $dono = User::factory()->donoQuadra()->create();
    $outroComprador = User::factory()->create();
    $pedido = criarPedidoComItem($dono, $outroComprador);

    $intruso = User::factory()->create();

    Livewire::actingAs($intruso)
        ->test(PerfilMensagens::class)
        ->set('novaMensagem', 'Oi')
        ->call('selecionarPedido', $pedido->id)
        ->assertForbidden();

    expect(Conversa::count())->toBe(0);
});

test('dono da loja vê a conversa sobre o pedido e consegue responder', function () {
    $dono = User::factory()->donoQuadra()->create();
    $comprador = User::factory()->create(['name' => 'Comprador Teste']);
    $pedido = criarPedidoComItem($dono, $comprador);

    $conversa = Conversa::factory()->create(['quadra_id' => null, 'pedido_id' => $pedido->id, 'jogador_id' => $comprador->id]);
    MensagemConversa::factory()->create(['conversa_id' => $conversa->id, 'user_id' => $comprador->id, 'texto' => 'Pergunta sobre o produto']);

    Livewire::actingAs($dono)
        ->test(PainelMensagens::class)
        ->assertSee('Comprador Teste')
        ->call('selecionarConversa', $conversa->id)
        ->assertSee('Pergunta sobre o produto')
        ->set('novaMensagem', 'Sim, tem garantia de 90 dias!')
        ->call('enviarMensagem')
        ->assertHasNoErrors()
        ->assertSee('Sim, tem garantia de 90 dias!');

    $resposta = MensagemConversa::latest('id')->first();

    expect($resposta->user_id)->toBe($dono->id)
        ->and($resposta->conversa_id)->toBe($conversa->id);
});

test('dono de outra loja não consegue ver nem responder a conversa sobre o pedido', function () {
    $dono = User::factory()->donoQuadra()->create();
    $outroDono = User::factory()->donoQuadra()->create();
    $comprador = User::factory()->create();
    $pedido = criarPedidoComItem($dono, $comprador);

    $conversa = Conversa::factory()->create(['quadra_id' => null, 'pedido_id' => $pedido->id, 'jogador_id' => $comprador->id]);

    expect(fn () => Livewire::actingAs($outroDono)
        ->test(PainelMensagens::class)
        ->call('selecionarConversa', $conversa->id)
    )->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
});
