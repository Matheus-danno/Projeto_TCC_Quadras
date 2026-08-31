<?php

use App\Models\User;

test('jogador não acessa o painel do dono', function () {
    $jogador = User::factory()->create();

    $this->actingAs($jogador)->get(route('painel.dashboard'))->assertForbidden();
});

test('dono de quadra acessa o próprio painel', function () {
    $dono = User::factory()->donoQuadra()->create();

    $this->actingAs($dono)->get(route('painel.dashboard'))->assertOk();
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
