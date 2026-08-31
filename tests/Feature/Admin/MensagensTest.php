<?php

use App\Livewire\Admin\Mensagens;
use App\Models\Conversa;
use App\Models\MensagemConversa;
use App\Models\Quadra;
use App\Models\User;
use Livewire\Livewire;

test('rota /admin/mensagens exige role admin', function () {
    $admin = User::factory()->admin()->create();
    $jogador = User::factory()->create();

    $this->actingAs($admin)->get(route('admin.mensagens'))->assertOk();
    $this->actingAs($jogador)->get(route('admin.mensagens'))->assertForbidden();
});

test('admin vê todas as conversas entre jogadores e donos de quadra', function () {
    $admin = User::factory()->admin()->create();
    $jogador = User::factory()->create(['name' => 'Ana Beatriz']);
    $dono = User::factory()->donoQuadra()->create(['name' => 'Carlos Andrade']);
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id, 'nome' => 'Arena Central']);

    $conversa = Conversa::factory()->create(['quadra_id' => $quadra->id, 'jogador_id' => $jogador->id]);
    MensagemConversa::factory()->create([
        'conversa_id' => $conversa->id,
        'user_id' => $jogador->id,
        'texto' => 'O estacionamento é gratuito?',
    ]);

    Livewire::actingAs($admin)
        ->test(Mensagens::class)
        ->assertSee('Ana Beatriz')
        ->assertSee('Carlos Andrade')
        ->assertSee('Arena Central')
        ->assertSee('O estacionamento é gratuito?');
});

test('admin consegue abrir uma conversa e ver todas as mensagens trocadas', function () {
    $admin = User::factory()->admin()->create();
    $jogador = User::factory()->create();
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id]);
    $conversa = Conversa::factory()->create(['quadra_id' => $quadra->id, 'jogador_id' => $jogador->id]);

    MensagemConversa::factory()->create(['conversa_id' => $conversa->id, 'user_id' => $jogador->id, 'texto' => 'Pergunta do jogador']);
    MensagemConversa::factory()->create(['conversa_id' => $conversa->id, 'user_id' => $dono->id, 'texto' => 'Resposta do dono']);

    Livewire::actingAs($admin)
        ->test(Mensagens::class)
        ->call('selecionarConversa', $conversa->id)
        ->assertSee('Pergunta do jogador')
        ->assertSee('Resposta do dono');
});

test('admin não pode ver conversa inexistente', function () {
    $admin = User::factory()->admin()->create();

    expect(fn () => Livewire::actingAs($admin)->test(Mensagens::class)->call('selecionarConversa', 999))
        ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
});
