<?php

use App\Models\Reserva;
use App\Models\User;

test('páginas de política de privacidade e termos de uso abrem sem login', function () {
    $this->get(route('politica.privacidade'))->assertOk()->assertSee('Política de Privacidade');
    $this->get(route('termos.uso'))->assertOk()->assertSee('Termos de Uso');
});

test('visitante não baixa dados', function () {
    $this->get(route('perfil.meus-dados'))->assertRedirect(route('login'));
});

test('usuário baixa os próprios dados em JSON, com endereço e reservas', function () {
    $user = User::factory()->create(['email' => 'ana@example.com', 'cpf' => '12345678909']);
    $user->endereco->update(['cidade' => 'Bauru']);
    Reserva::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->get(route('perfil.meus-dados'));

    $response->assertOk()
        ->assertDownload('meus-dados-alugaquadra.json');

    $dados = json_decode($response->streamedContent(), true);

    expect($dados['conta']['email'])->toBe('ana@example.com')
        ->and($dados['conta']['cpf'])->toBe('12345678909')
        ->and($dados['endereco']['cidade'])->toBe('Bauru')
        ->and($dados['reservas'])->toHaveCount(1);
});

test('a exportação não inclui senha nem segredos de autenticação', function () {
    $user = User::factory()->create();

    $conteudo = $this->actingAs($user)->get(route('perfil.meus-dados'))->streamedContent();
    $dados = json_decode($conteudo, true);

    expect($dados['conta'])->not->toHaveKeys(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])
        ->and($conteudo)->not->toContain($user->password);
});

test('a exportação traz só os dados de quem pediu', function () {
    $user = User::factory()->create();
    $outro = User::factory()->create(['email' => 'outra.pessoa@example.com']);
    Reserva::factory()->create(['user_id' => $outro->id]);

    $conteudo = $this->actingAs($user)->get(route('perfil.meus-dados'))->streamedContent();

    expect($conteudo)->not->toContain('outra.pessoa@example.com')
        ->and(json_decode($conteudo, true)['reservas'])->toBeEmpty();
});
