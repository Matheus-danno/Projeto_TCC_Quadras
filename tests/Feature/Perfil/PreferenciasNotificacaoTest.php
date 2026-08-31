<?php

use App\Livewire\Perfil\Notificacoes;
use App\Models\User;
use Livewire\Livewire;

test('preferências de notificação vêm todas habilitadas por padrão', function () {
    $user = User::factory()->create()->fresh();

    Livewire::actingAs($user)
        ->test(Notificacoes::class)
        ->assertSee('Confirmação de Reserva')
        ->assertSee('Lembrete antes do horário')
        ->assertSee('Novo jogador confirmado na sala')
        ->assertSee('Mensagens de grupo')
        ->assertSee('Ofertas e novidades');

    expect($user->notif_confirmacao_reserva)->toBeTrue()
        ->and($user->notif_lembrete_horario)->toBeTrue()
        ->and($user->notif_novo_jogador_sala)->toBeTrue()
        ->and($user->notif_mensagens_grupo)->toBeTrue()
        ->and($user->notif_ofertas_novidades)->toBeTrue();
});

test('usuário consegue desabilitar uma preferência de notificação', function () {
    $user = User::factory()->create()->fresh();

    Livewire::actingAs($user)
        ->test(Notificacoes::class)
        ->call('alternarPreferencia', 'notif_confirmacao_reserva')
        ->assertHasNoErrors();

    expect($user->fresh()->notif_confirmacao_reserva)->toBeFalse();
});

test('alternar preferência não afeta as outras', function () {
    $user = User::factory()->create()->fresh();

    Livewire::actingAs($user)
        ->test(Notificacoes::class)
        ->call('alternarPreferencia', 'notif_mensagens_grupo');

    $user->refresh();

    expect($user->notif_mensagens_grupo)->toBeFalse()
        ->and($user->notif_confirmacao_reserva)->toBeTrue()
        ->and($user->notif_lembrete_horario)->toBeTrue()
        ->and($user->notif_novo_jogador_sala)->toBeTrue()
        ->and($user->notif_ofertas_novidades)->toBeTrue();
});

test('usuário consegue reabilitar uma preferência desabilitada', function () {
    $user = User::factory()->create(['notif_ofertas_novidades' => false]);

    Livewire::actingAs($user)
        ->test(Notificacoes::class)
        ->call('alternarPreferencia', 'notif_ofertas_novidades');

    expect($user->fresh()->notif_ofertas_novidades)->toBeTrue();
});

test('campo desconhecido é rejeitado ao alternar preferência', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Notificacoes::class)
        ->call('alternarPreferencia', 'role')
        ->assertForbidden();

    expect($user->fresh()->role)->toBe($user->role);
});
