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

    expect($user->notificacoes)->toBeNull();
});

test('usuário consegue desabilitar uma preferência de notificação', function () {
    $user = User::factory()->create()->fresh();

    Livewire::actingAs($user)
        ->test(Notificacoes::class)
        ->call('alternarPreferencia', 'notif_confirmacao_reserva')
        ->assertHasNoErrors();

    expect($user->notificacoes()->first()->notif_confirmacao_reserva)->toBeFalse();
});

test('alternar preferência não afeta as outras', function () {
    $user = User::factory()->create()->fresh();

    Livewire::actingAs($user)
        ->test(Notificacoes::class)
        ->call('alternarPreferencia', 'notif_mensagens_grupo');

    $notificacoes = $user->notificacoes()->first();

    expect($notificacoes->notif_mensagens_grupo)->toBeFalse()
        ->and($notificacoes->notif_confirmacao_reserva)->toBeTrue()
        ->and($notificacoes->notif_lembrete_horario)->toBeTrue()
        ->and($notificacoes->notif_novo_jogador_sala)->toBeTrue()
        ->and($notificacoes->notif_ofertas_novidades)->toBeTrue();
});

test('usuário consegue reabilitar uma preferência desabilitada', function () {
    $user = User::factory()->create();
    $user->notificacoes()->create(['notif_ofertas_novidades' => false]);

    Livewire::actingAs($user)
        ->test(Notificacoes::class)
        ->call('alternarPreferencia', 'notif_ofertas_novidades');

    expect($user->notificacoes()->first()->notif_ofertas_novidades)->toBeTrue();
});

test('campo desconhecido é rejeitado ao alternar preferência', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Notificacoes::class)
        ->call('alternarPreferencia', 'campo_invalido')
        ->assertForbidden();

    expect($user->notificacoes)->toBeNull();
});
