<?php

use App\Livewire\Perfil\Seguranca;
use App\Models\User;
use Laravel\Fortify\Fortify;
use Livewire\Livewire;
use PragmaRX\Google2FA\Google2FA;

test('usuário consegue atualizar a senha informando a senha atual correta', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Seguranca::class)
        ->set('current_password', 'password')
        ->set('password', 'nova-senha-123')
        ->set('password_confirmation', 'nova-senha-123')
        ->call('atualizarSenha')
        ->assertHasNoErrors();

    expect(\Illuminate\Support\Facades\Hash::check('nova-senha-123', $user->fresh()->password))->toBeTrue();
});

test('atualização de senha falha com senha atual incorreta', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Seguranca::class)
        ->set('current_password', 'senha-errada')
        ->set('password', 'nova-senha-123')
        ->set('password_confirmation', 'nova-senha-123')
        ->call('atualizarSenha')
        ->assertHasErrors('current_password');

    expect(\Illuminate\Support\Facades\Hash::check('password', $user->fresh()->password))->toBeTrue();
});

test('atualização de senha falha quando a confirmação não bate', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Seguranca::class)
        ->set('current_password', 'password')
        ->set('password', 'nova-senha-123')
        ->set('password_confirmation', 'outra-senha')
        ->call('atualizarSenha')
        ->assertHasErrors('password');
});

test('autenticação de dois fatores começa desabilitada', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Seguranca::class)
        ->assertSet('twoFactorEnabled', false)
        ->assertSee('Desativada');
});

test('iniciar configuração de 2fa gera qr code e chave manual', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Seguranca::class)
        ->call('iniciarConfiguracao2fa')
        ->assertSet('mostrarConfiguracao2fa', true);

    $user->refresh();

    expect($user->two_factor_secret)->not->toBeNull()
        ->and($user->two_factor_confirmed_at)->toBeNull();
});

test('usuário consegue confirmar a configuração de 2fa com um código válido', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)
        ->test(Seguranca::class)
        ->call('iniciarConfiguracao2fa');

    $segredo = Fortify::currentEncrypter()->decrypt($user->fresh()->two_factor_secret);
    $codigo = (new Google2FA)->getCurrentOtp($segredo);

    $component->set('codigo', $codigo)
        ->call('confirmar2fa')
        ->assertHasNoErrors()
        ->assertSet('twoFactorEnabled', true);

    expect($user->fresh()->two_factor_confirmed_at)->not->toBeNull();
});

test('confirmação de 2fa com código inválido é rejeitada', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)
        ->test(Seguranca::class)
        ->call('iniciarConfiguracao2fa')
        ->set('codigo', '000000')
        ->call('confirmar2fa');

    $component->assertSet('twoFactorEnabled', false);

    expect($user->fresh()->two_factor_confirmed_at)->toBeNull();
});

test('cancelar configuração de 2fa apaga o segredo gerado', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Seguranca::class)
        ->call('iniciarConfiguracao2fa')
        ->call('cancelarConfiguracao2fa')
        ->assertSet('mostrarConfiguracao2fa', false);

    expect($user->fresh()->two_factor_secret)->toBeNull();
});

test('usuário consegue desativar a 2fa depois de confirmada', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Seguranca::class)->call('iniciarConfiguracao2fa');

    $segredo = Fortify::currentEncrypter()->decrypt($user->fresh()->two_factor_secret);
    $codigo = (new Google2FA)->getCurrentOtp($segredo);

    $component->set('codigo', $codigo)->call('confirmar2fa');

    $component->call('desativar2fa')->assertSet('twoFactorEnabled', false);

    $user->refresh();

    expect($user->two_factor_secret)->toBeNull()
        ->and($user->two_factor_confirmed_at)->toBeNull();
});

test('usuário consegue ver e regenerar códigos de recuperação', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Seguranca::class)->call('iniciarConfiguracao2fa');

    $segredo = Fortify::currentEncrypter()->decrypt($user->fresh()->two_factor_secret);
    $codigo = (new Google2FA)->getCurrentOtp($segredo);

    $component->set('codigo', $codigo)->call('confirmar2fa');

    $component->call('mostrarRecuperacao');
    $codigosIniciais = $component->get('codigosRecuperacao');

    expect($codigosIniciais)->toHaveCount(8);

    $component->call('gerarNovosCodigos');
    $novosCodigos = $component->get('codigosRecuperacao');

    expect($novosCodigos)->toHaveCount(8)
        ->and($novosCodigos)->not->toEqual($codigosIniciais);
});

test('configuração de 2fa abandonada é limpa ao recarregar o componente', function () {
    $user = User::factory()->create();

    $user->forceFill([
        'two_factor_secret' => encrypt('segredo-de-teste'),
        'two_factor_recovery_codes' => encrypt(json_encode(['codigo1', 'codigo2'])),
        'two_factor_confirmed_at' => null,
    ])->save();

    Livewire::actingAs($user)
        ->test(Seguranca::class)
        ->assertSet('twoFactorEnabled', false);

    $user->refresh();

    expect($user->two_factor_secret)->toBeNull()
        ->and($user->two_factor_recovery_codes)->toBeNull();
});
