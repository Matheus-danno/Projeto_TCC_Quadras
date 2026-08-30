<?php

use App\Models\User;

test('jogador não acessa o painel do dono nem a área de admin', function () {
    $jogador = User::factory()->create();

    $this->actingAs($jogador)->get(route('painel.dashboard'))->assertForbidden();
    $this->actingAs($jogador)->get(route('admin.dashboard'))->assertForbidden();
});

test('dono de quadra acessa o próprio painel mas não a área de admin', function () {
    $dono = User::factory()->donoQuadra()->create();

    $this->actingAs($dono)->get(route('painel.dashboard'))->assertOk();
    $this->actingAs($dono)->get(route('admin.dashboard'))->assertForbidden();
});

test('admin acessa a área de admin mas não o painel do dono', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
    $this->actingAs($admin)->get(route('painel.dashboard'))->assertForbidden();
});

test('login redireciona jogador para a listagem de quadras', function () {
    $jogador = User::factory()->create();

    $this->post(route('login.store'), ['email' => $jogador->email, 'password' => 'password'])
        ->assertRedirect(route('quadras.index', absolute: false));
});

test('login redireciona dono de quadra para o próprio painel', function () {
    $dono = User::factory()->donoQuadra()->create();

    $this->post(route('login.store'), ['email' => $dono->email, 'password' => 'password'])
        ->assertRedirect(route('painel.dashboard', absolute: false));
});

test('login redireciona admin para a área de administração', function () {
    $admin = User::factory()->admin()->create();

    $this->post(route('login.store'), ['email' => $admin->email, 'password' => 'password'])
        ->assertRedirect(route('admin.dashboard', absolute: false));
});
