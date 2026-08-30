<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('jogador é redirecionado da rota genérica dashboard para a listagem de quadras', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('quadras.index'));
});

test('dono de quadra é redirecionado da rota genérica dashboard para o painel', function () {
    $dono = User::factory()->donoQuadra()->create();

    $this->actingAs($dono)
        ->get(route('dashboard'))
        ->assertRedirect(route('painel.dashboard'));
});

test('admin é redirecionado da rota genérica dashboard para a área administrativa', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertRedirect(route('admin.dashboard'));
});
