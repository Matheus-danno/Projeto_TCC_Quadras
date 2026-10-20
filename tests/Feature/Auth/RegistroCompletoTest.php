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
        'logradouro' => 'Rua das Acácias',
        'numero' => '45',
        'complemento' => '',
        'bairro' => 'Centro',
        'cep' => '50000-000',
        'cidade' => 'Recife',
        'estado' => 'PE',
        'telefone' => '(81) 98888-7777',
        'password' => 'SenhaForte123',
        'password_confirmation' => 'SenhaForte123',
        'aceitaTermos' => true,
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
        ->and($user->endereco->logradouro)->toBe('Rua das Acácias')
        ->and($user->endereco->numero)->toBe('45')
        ->and($user->endereco->bairro)->toBe('Centro')
        ->and($user->endereco->cidade)->toBe('Recife')
        ->and($user->endereco->estado)->toBe('PE')
        ->and($user->endereco->cep)->toBe('50000000')
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

test('rejeita cpf com 11 dígitos mas dígito verificador errado', function () {
    $component = Livewire::test(Registrar::class);

    foreach (dadosValidosDeCadastro(['cpf' => '123.456.789-00']) as $campo => $valor) {
        $component->set($campo, $valor);
    }

    $component->call('registrar')->assertHasErrors('cpf');

    expect(User::where('email', 'fernanda.lima@example.com')->exists())->toBeFalse();
});

test('rejeita estado que não é uma UF brasileira', function () {
    $component = Livewire::test(Registrar::class);

    foreach (dadosValidosDeCadastro(['estado' => 'XX']) as $campo => $valor) {
        $component->set($campo, $valor);
    }

    $component->call('registrar')->assertHasErrors('estado');
});

test('rejeita quem ainda não tem a idade mínima', function () {
    $quaseNaIdade = now()->subYears(Registrar::IDADE_MINIMA)->addDay();

    $component = Livewire::test(Registrar::class);

    foreach (dadosValidosDeCadastro([
        'diaNascimento' => (string) $quaseNaIdade->day,
        'mesNascimento' => (string) $quaseNaIdade->month,
        'anoNascimento' => (string) $quaseNaIdade->year,
    ]) as $campo => $valor) {
        $component->set($campo, $valor);
    }

    $component->call('registrar')->assertHasErrors('diaNascimento');

    expect(User::where('email', 'fernanda.lima@example.com')->exists())->toBeFalse();
});

test('aceita quem completa a idade mínima hoje', function () {
    $aniversario = now()->subYears(Registrar::IDADE_MINIMA);

    $component = Livewire::test(Registrar::class);

    foreach (dadosValidosDeCadastro([
        'diaNascimento' => (string) $aniversario->day,
        'mesNascimento' => (string) $aniversario->month,
        'anoNascimento' => (string) $aniversario->year,
    ]) as $campo => $valor) {
        $component->set($campo, $valor);
    }

    $component->call('registrar')->assertHasNoErrors();

    expect(User::where('email', 'fernanda.lima@example.com')->exists())->toBeTrue();
});

test('rejeita cadastro sem aceitar a política de privacidade e os termos', function () {
    $component = Livewire::test(Registrar::class);

    foreach (dadosValidosDeCadastro(['aceitaTermos' => false]) as $campo => $valor) {
        $component->set($campo, $valor);
    }

    $component->call('registrar')->assertHasErrors('aceitaTermos');

    expect(User::where('email', 'fernanda.lima@example.com')->exists())->toBeFalse();
});

test('cadastro registra quando e qual versão dos termos foi aceita', function () {
    $component = Livewire::test(Registrar::class);

    foreach (dadosValidosDeCadastro() as $campo => $valor) {
        $component->set($campo, $valor);
    }

    $component->call('registrar')->assertHasNoErrors();

    $user = User::where('email', 'fernanda.lima@example.com')->first();

    expect($user->consentimento_em)->not->toBeNull()
        ->and($user->consentimento_versao)->toBe(config('privacidade.versao'));
});
