<?php

use App\Livewire\Admin\Usuarios\Listagem;
use App\Models\User;
use Livewire\Livewire;

test('usuário bloqueado não consegue autenticar', function () {
    $jogador = User::factory()->create(['bloqueado_em' => now()]);

    $this->post(route('login.store'), [
        'email' => $jogador->email,
        'password' => 'password',
        'contexto' => 'jogador',
    ])->assertSessionHasErrorsIn('email');

    $this->assertGuest();
});

test('bloquear um usuário já logado derruba a sessão dele na próxima requisição', function () {
    $jogador = User::factory()->create();

    $this->actingAs($jogador)->get(route('quadras.index'))->assertOk();

    $jogador->update(['bloqueado_em' => now()]);

    $this->get(route('quadras.index'))->assertRedirect(route('home'));
    $this->assertGuest();
});

test('admin consegue bloquear e desbloquear um usuário pela tela', function () {
    $admin = User::factory()->admin()->create();
    $jogador = User::factory()->create();

    Livewire::actingAs($admin)->test(Listagem::class)
        ->call('bloquear', $jogador->id);

    expect($jogador->refresh()->estaBloqueado())->toBeTrue();

    Livewire::actingAs($admin)->test(Listagem::class)
        ->call('desbloquear', $jogador->id);

    expect($jogador->refresh()->estaBloqueado())->toBeFalse();
});

test('admin não pode bloquear outro admin', function () {
    $admin = User::factory()->admin()->create();
    $outroAdmin = User::factory()->admin()->create();

    Livewire::actingAs($admin)->test(Listagem::class)
        ->call('bloquear', $outroAdmin->id)
        ->assertForbidden();
});
