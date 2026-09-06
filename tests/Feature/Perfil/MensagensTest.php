<?php

use App\Livewire\Perfil\Mensagens;
use App\Models\Conversa;
use App\Models\MensagemConversa;
use App\Models\Quadra;
use App\Models\Sala;
use App\Models\User;
use Livewire\Livewire;

test('rota perfil renderiza o componente de mensagens quando o jogador já criou uma sala', function () {
    $jogador = User::factory()->create();
    Sala::factory()->create(['criador_id' => $jogador->id]);

    $this->actingAs($jogador)
        ->get(route('perfil'))
        ->assertOk()
        ->assertSeeLivewire(Mensagens::class);
});

test('mensagens do dono contam como não lidas até o jogador abrir a conversa', function () {
    $dono = User::factory()->donoQuadra()->create();
    $jogador = User::factory()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id]);

    Sala::factory()->create(['quadra_id' => $quadra->id, 'criador_id' => $jogador->id]);

    $conversa = Conversa::factory()->create(['quadra_id' => $quadra->id, 'jogador_id' => $jogador->id]);

    MensagemConversa::factory()->create(['conversa_id' => $conversa->id, 'user_id' => $dono->id]);
    MensagemConversa::factory()->create(['conversa_id' => $conversa->id, 'user_id' => $dono->id]);

    $component = Livewire::actingAs($jogador)->test(Mensagens::class);

    $quadraNaLista = $component->instance()->quadras->firstWhere('id', $quadra->id);
    expect($quadraNaLista->naoLidas)->toBe(2);

    $component->call('selecionarQuadra', $quadra->id);

    expect($conversa->fresh()->mensagensNaoLidasPara($jogador->id))->toBe(0);

    unset($component->instance()->quadras);
    $quadraAtualizada = $component->instance()->quadras->firstWhere('id', $quadra->id);
    expect($quadraAtualizada->naoLidas)->toBe(0);
});

test('mensagens enviadas pelo próprio jogador não contam como não lidas', function () {
    $dono = User::factory()->donoQuadra()->create();
    $jogador = User::factory()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id]);

    Sala::factory()->create(['quadra_id' => $quadra->id, 'criador_id' => $jogador->id]);

    $conversa = Conversa::factory()->create(['quadra_id' => $quadra->id, 'jogador_id' => $jogador->id]);

    MensagemConversa::factory()->create(['conversa_id' => $conversa->id, 'user_id' => $jogador->id]);

    $quadraNaLista = Livewire::actingAs($jogador)
        ->test(Mensagens::class)
        ->instance()
        ->quadras
        ->firstWhere('id', $quadra->id);

    expect($quadraNaLista->naoLidas)->toBe(0);
});
