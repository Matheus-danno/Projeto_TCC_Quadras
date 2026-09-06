<?php

use App\Models\User;
use Laravel\Fortify\Features;

test('login screen can be rendered', function () {
    $response = $this->get(route('login'));

    $response->assertOk();
});

test('login screen has a link to the dono de quadra login screen', function () {
    $response = $this->get(route('login'));

    $response->assertOk()->assertSee(route('login.dono'), escape: false);
});

test('login screen has a link to the privacy policy page', function () {
    $response = $this->get(route('login'));

    $response->assertOk()->assertSee(route('politica.privacidade'), escape: false);
});

test('privacy policy page can be rendered', function () {
    $response = $this->get(route('politica.privacidade'));

    $response->assertOk()->assertSee('Política de Privacidade');
});

test('dono de quadra login screen can be rendered', function () {
    $response = $this->get(route('login.dono'));

    $response->assertOk()->assertSee('Painel do Dono de Quadra');
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('quadras.index', absolute: false));

    $this->assertAuthenticated();
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrorsIn('email');

    $this->assertGuest();
});

test('users with two factor enabled are redirected to two factor challenge', function () {
    if (! Features::canManageTwoFactorAuthentication()) {
        $this->markTestSkipped('Two-factor authentication is not enabled.');
    }

    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);

    $user = User::factory()->withTwoFactor()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('two-factor.login'));
    $this->assertGuest();
});

test('usuário autenticado é redirecionado ao acessar a tela de login', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('login'))
        ->assertRedirect(route('dashboard'));

    $this->actingAs($user)
        ->get(route('login.dono'))
        ->assertRedirect(route('dashboard'));
});

test('dono de quadra não consegue entrar pela tela de login do cliente', function () {
    $dono = User::factory()->donoQuadra()->create();

    $response = $this->post(route('login.store'), [
        'contexto' => 'jogador',
        'email' => $dono->email,
        'password' => 'password',
    ]);

    $response->assertSessionHasErrorsIn('email');

    $this->assertGuest();
});

test('jogador não consegue entrar pela tela de login do dono', function () {
    $jogador = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'contexto' => 'dono',
        'email' => $jogador->email,
        'password' => 'password',
    ]);

    $response->assertSessionHasErrorsIn('email');

    $this->assertGuest();
});

test('dono de quadra consegue entrar pela tela de login do dono', function () {
    $dono = User::factory()->donoQuadra()->create();

    $response = $this->post(route('login.store'), [
        'contexto' => 'dono',
        'email' => $dono->email,
        'password' => 'password',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('painel.dashboard', absolute: false));

    $this->assertAuthenticatedAs($dono);
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('logout'));

    $response->assertRedirect(route('home'));

    $this->assertGuest();
});
