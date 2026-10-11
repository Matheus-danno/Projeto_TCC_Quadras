<?php

use App\Livewire\Admin\Dashboard;
use App\Models\User;

test('visitante não autenticado é redirecionado ao tentar acessar o admin', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
});

test('jogador não pode acessar a área do admin', function () {
    $jogador = User::factory()->create();

    $this->actingAs($jogador)->get(route('admin.dashboard'))->assertForbidden();
});

test('dono de quadra não pode acessar a área do admin', function () {
    $dono = User::factory()->donoQuadra()->create();

    $this->actingAs($dono)->get(route('admin.dashboard'))->assertForbidden();
});

test('administrador acessa o dashboard do admin', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSeeLivewire(Dashboard::class);
});

test('administrador entra pela tela de login do admin', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->post(route('login.store'), [
        'email' => $admin->email,
        'password' => 'password',
        'contexto' => 'admin',
    ]);

    $response->assertSessionHasNoErrors()->assertRedirect(route('admin.dashboard', absolute: false));
    $this->assertAuthenticatedAs($admin);
});

test('administrador não consegue entrar pela tela de login de cliente ou de dono', function () {
    $admin = User::factory()->admin()->create();

    $this->post(route('login.store'), [
        'email' => $admin->email,
        'password' => 'password',
        'contexto' => 'jogador',
    ])->assertSessionHasErrorsIn('email');

    $this->assertGuest();
});
