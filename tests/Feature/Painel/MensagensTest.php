<?php

use App\Livewire\Painel\Mensagens;
use App\Models\Conversa;
use App\Models\MensagemConversa;
use App\Models\Quadra;
use App\Models\User;
use Livewire\Livewire;

test('rota painel.mensagens renderiza o componente', function () {
    $dono = User::factory()->donoQuadra()->create();

    $this->actingAs($dono)
        ->get(route('painel.mensagens'))
        ->assertOk()
        ->assertSeeLivewire(Mensagens::class);
});

test('busca filtra as conversas por nome do jogador ou nome da quadra', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id, 'nome' => 'Arena Central']);
    $outraQuadra = Quadra::factory()->create(['dono_id' => $dono->id, 'nome' => 'Quadra Norte']);

    $jogadorFulano = User::factory()->create(['name' => 'Fulano Jogador']);
    $jogadorBeltrano = User::factory()->create(['name' => 'Beltrano Jogador']);

    Conversa::factory()->create(['quadra_id' => $quadra->id, 'jogador_id' => $jogadorFulano->id]);
    Conversa::factory()->create(['quadra_id' => $outraQuadra->id, 'jogador_id' => $jogadorBeltrano->id]);

    $conversas = Livewire::actingAs($dono)
        ->test(Mensagens::class)
        ->set('busca', 'Fulano')
        ->instance()
        ->conversas;

    expect($conversas)->toHaveCount(1)
        ->and($conversas->first()->jogador->name)->toBe('Fulano Jogador');

    $conversasPorQuadra = Livewire::actingAs($dono)
        ->test(Mensagens::class)
        ->set('busca', 'Norte')
        ->instance()
        ->conversas;

    expect($conversasPorQuadra)->toHaveCount(1)
        ->and($conversasPorQuadra->first()->quadra->nome)->toBe('Quadra Norte');
});

test('mensagens do jogador contam como não lidas até o dono abrir a conversa', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id]);
    $jogador = User::factory()->create();

    $conversa = Conversa::factory()->create(['quadra_id' => $quadra->id, 'jogador_id' => $jogador->id]);

    MensagemConversa::factory()->create(['conversa_id' => $conversa->id, 'user_id' => $jogador->id]);
    MensagemConversa::factory()->create(['conversa_id' => $conversa->id, 'user_id' => $jogador->id]);

    $component = Livewire::actingAs($dono)->test(Mensagens::class);

    $conversaNaLista = $component->instance()->conversas->firstWhere('id', $conversa->id);
    expect($conversaNaLista->mensagensNaoLidasPara($dono->id))->toBe(2);

    $component->call('selecionarConversa', $conversa->id);

    expect($conversa->fresh()->mensagensNaoLidasPara($dono->id))->toBe(0);

    unset($component->instance()->conversas);
    $conversaAtualizada = $component->instance()->conversas->firstWhere('id', $conversa->id);
    expect($conversaAtualizada->mensagensNaoLidasPara($dono->id))->toBe(0);
});

test('mensagens enviadas pelo próprio dono não contam como não lidas', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id]);
    $jogador = User::factory()->create();

    $conversa = Conversa::factory()->create(['quadra_id' => $quadra->id, 'jogador_id' => $jogador->id]);

    MensagemConversa::factory()->create(['conversa_id' => $conversa->id, 'user_id' => $dono->id]);

    expect($conversa->mensagensNaoLidasPara($dono->id))->toBe(0);
});

test('dono não vê conversas de quadras de outro dono', function () {
    $dono = User::factory()->donoQuadra()->create();
    $outroDono = User::factory()->donoQuadra()->create();
    $quadraAlheia = Quadra::factory()->create(['dono_id' => $outroDono->id]);

    $conversaAlheia = Conversa::factory()->create(['quadra_id' => $quadraAlheia->id]);

    expect(fn () => Livewire::actingAs($dono)->test(Mensagens::class)->call('selecionarConversa', $conversaAlheia->id))
        ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
});
