<?php

use App\Livewire\Admin\Mensagens;
use App\Models\SuporteMensagem;
use App\Models\User;
use Livewire\Livewire;

test('rota /admin/mensagens exige role admin', function () {
    $admin = User::factory()->admin()->create();
    $jogador = User::factory()->create();

    $this->actingAs($admin)->get(route('admin.mensagens'))->assertOk();
    $this->actingAs($jogador)->get(route('admin.mensagens'))->assertForbidden();
});

test('admin vê as mensagens de suporte pendentes por padrão', function () {
    $admin = User::factory()->admin()->create();
    $jogador = User::factory()->create(['name' => 'Ana Beatriz']);

    $pendente = SuporteMensagem::factory()->create([
        'user_id' => $jogador->id,
        'assunto' => 'Dúvida sobre pagamento',
    ]);
    $respondida = SuporteMensagem::factory()->create([
        'user_id' => $jogador->id,
        'assunto' => 'Já resolvido',
        'respondida_em' => now(),
    ]);

    $component = Livewire::actingAs($admin)
        ->test(Mensagens::class)
        ->assertSee('Dúvida sobre pagamento')
        ->assertSee('Ana Beatriz')
        ->assertDontSee('Já resolvido');

    expect($component->instance()->mensagens())->toHaveCount(1)
        ->and($component->instance()->mensagens()->first()->id)->toBe($pendente->id);
});

test('admin consegue marcar uma mensagem como respondida', function () {
    $admin = User::factory()->admin()->create();
    $mensagem = SuporteMensagem::factory()->create();

    Livewire::actingAs($admin)
        ->test(Mensagens::class)
        ->call('marcarComoRespondida', $mensagem->id)
        ->assertHasNoErrors();

    expect($mensagem->fresh()->respondida_em)->not->toBeNull();
});

test('admin consegue reabrir uma mensagem já respondida', function () {
    $admin = User::factory()->admin()->create();
    $mensagem = SuporteMensagem::factory()->create(['respondida_em' => now()]);

    Livewire::actingAs($admin)
        ->test(Mensagens::class)
        ->call('marcarComoPendente', $mensagem->id);

    expect($mensagem->fresh()->respondida_em)->toBeNull();
});

test('filtro de status mostra apenas as mensagens respondidas', function () {
    $admin = User::factory()->admin()->create();

    SuporteMensagem::factory()->create(['assunto' => 'Pendente aqui']);
    SuporteMensagem::factory()->create(['assunto' => 'Respondida aqui', 'respondida_em' => now()]);

    Livewire::actingAs($admin)
        ->test(Mensagens::class)
        ->set('filtroStatus', 'respondidas')
        ->assertSee('Respondida aqui')
        ->assertDontSee('Pendente aqui');
});
