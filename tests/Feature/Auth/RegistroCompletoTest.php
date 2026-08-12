<?php

use App\Livewire\Auth\Registrar;
use App\Models\User;
use Livewire\Livewire;

function dadosValidosDeCadastro(array $sobrescrever = []): array
{
    return array_merge([
        'name' => 'Fernanda Lima',
        'email' => 'fernanda.lima@example.com',
        'cpf' => '123.456.789-09',
        'diaNascimento' => '15',
        'mesNascimento' => '6',
        'anoNascimento' => '1998',
        'sexo' => 'feminino',
        'endereco' => 'Rua das Acácias, 45 - Centro',
        'cep' => '50000-000',
        'cidade' => 'Recife',
        'estado' => 'PE',
        'telefone' => '(81) 98888-7777',
        'password' => 'SenhaForte123',
        'password_confirmation' => 'SenhaForte123',
    ], $sobrescrever);
}

test('cadastro completo cria usuário com todos os dados e loga automaticamente', function () {
    $component = Livewire::test(Registrar::class);

    foreach (dadosValidosDeCadastro() as $campo => $valor) {
        $component->set($campo, $valor);
    }

    $component->call('registrar')->assertHasNoErrors();

    $user = User::where('email', 'fernanda.lima@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('Fernanda Lima')
        ->and($user->cpf)->toBe('12345678909')
        ->and($user->data_nascimento->format('Y-m-d'))->toBe('1998-06-15')
        ->and($user->sexo->value)->toBe('feminino')
        ->and($user->cidade)->toBe('Recife')
        ->and($user->estado)->toBe('PE')
        ->and($user->cep)->toBe('50000000')
        ->and($user->telefone)->toBe('81988887777');

    expect(auth()->check())->toBeTrue();
    expect(auth()->id())->toBe($user->id);
});

test('rejeita data de nascimento inválida', function () {
    $component = Livewire::test(Registrar::class);

    foreach (dadosValidosDeCadastro(['diaNascimento' => '31', 'mesNascimento' => '2']) as $campo => $valor) {
        $component->set($campo, $valor);
    }

    $component->call('registrar')->assertHasErrors('diaNascimento');

    expect(User::where('email', 'fernanda.lima@example.com')->exists())->toBeFalse();
});

test('rejeita cpf com menos de 11 dígitos', function () {
    $component = Livewire::test(Registrar::class);

    foreach (dadosValidosDeCadastro(['cpf' => '123.456']) as $campo => $valor) {
        $component->set($campo, $valor);
    }

    $component->call('registrar')->assertHasErrors('cpf');
});

test('rejeita e-mail já cadastrado', function () {
    User::factory()->create(['email' => 'fernanda.lima@example.com']);

    $component = Livewire::test(Registrar::class);

    foreach (dadosValidosDeCadastro() as $campo => $valor) {
        $component->set($campo, $valor);
    }

    $component->call('registrar')->assertHasErrors('email');
});
