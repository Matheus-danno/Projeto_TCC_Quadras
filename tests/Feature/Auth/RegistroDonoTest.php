<?php

use App\Enums\UserRole;
use App\Livewire\Auth\RegistrarDono;
use App\Models\User;
use Livewire\Livewire;

function dadosValidosDeCadastroDono(array $sobrescrever = []): array
{
    return array_merge([
        'nomeEstabelecimento' => 'Arena Sports Bauru',
        'cnpj' => '12.345.678/0001-90',
        'telefone' => '(14) 99711-2233',
        'name' => 'João Silva',
        'email' => 'joao.silva@example.com',
        'endereco' => 'Rua Correia Júnior, 357 - Centro',
        'cidade' => 'Bauru',
        'estado' => 'SP',
        'password' => 'SenhaForte123',
        'password_confirmation' => 'SenhaForte123',
    ], $sobrescrever);
}

test('cadastro de estabelecimento cria dono de quadra e loga automaticamente', function () {
    $component = Livewire::test(RegistrarDono::class);

    foreach (dadosValidosDeCadastroDono() as $campo => $valor) {
        $component->set($campo, $valor);
    }

    $component->call('registrar')->assertHasNoErrors();

    $user = User::where('email', 'joao.silva@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('João Silva')
        ->and($user->nome_estabelecimento)->toBe('Arena Sports Bauru')
        ->and($user->cnpj)->toBe('12345678000190')
        ->and($user->role)->toBe(UserRole::DonoQuadra)
        ->and($user->cidade)->toBe('Bauru')
        ->and($user->estado)->toBe('SP')
        ->and($user->telefone)->toBe('14997112233');

    expect(auth()->check())->toBeTrue();
    expect(auth()->id())->toBe($user->id);
});

test('rejeita cnpj com menos de 14 dígitos', function () {
    $component = Livewire::test(RegistrarDono::class);

    foreach (dadosValidosDeCadastroDono(['cnpj' => '12.345']) as $campo => $valor) {
        $component->set($campo, $valor);
    }

    $component->call('registrar')->assertHasErrors('cnpj');

    expect(User::where('email', 'joao.silva@example.com')->exists())->toBeFalse();
});

test('rejeita cnpj já cadastrado', function () {
    User::factory()->create(['cnpj' => '12345678000190']);

    $component = Livewire::test(RegistrarDono::class);

    foreach (dadosValidosDeCadastroDono() as $campo => $valor) {
        $component->set($campo, $valor);
    }

    $component->call('registrar')->assertHasErrors('cnpj');
});

test('rejeita e-mail já cadastrado', function () {
    User::factory()->create(['email' => 'joao.silva@example.com']);

    $component = Livewire::test(RegistrarDono::class);

    foreach (dadosValidosDeCadastroDono() as $campo => $valor) {
        $component->set($campo, $valor);
    }

    $component->call('registrar')->assertHasErrors('email');
});

test('tela de cadastro do dono pode ser renderizada', function () {
    $this->get(route('cadastro.dono'))->assertOk();
});
