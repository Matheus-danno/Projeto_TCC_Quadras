<?php

use App\Livewire\Perfil\Pagamentos;
use App\Models\Cartao;
use App\Models\User;
use Livewire\Livewire;

test('primeiro cartão cadastrado vira principal automaticamente', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Pagamentos::class)
        ->set('numeroCartao', '4111111111111111')
        ->set('nomeCartao', 'Ana Beatriz Souza')
        ->set('validade', '12/30')
        ->set('cvv', '123')
        ->call('adicionarCartao')
        ->assertHasNoErrors();

    expect(Cartao::count())->toBe(1);

    $cartao = Cartao::first();

    expect($cartao->user_id)->toBe($user->id)
        ->and($cartao->numero_final)->toBe('1111')
        ->and($cartao->bandeira)->toBe('visa')
        ->and($cartao->nome_titular)->toBe('Ana Beatriz Souza')
        ->and($cartao->validade)->toBe('12/30')
        ->and($cartao->principal)->toBeTrue();
});

test('número completo e cvv do cartão não são persistidos no banco', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Pagamentos::class)
        ->set('numeroCartao', '4111111111111111')
        ->set('nomeCartao', 'Ana Beatriz Souza')
        ->set('validade', '12/30')
        ->set('cvv', '123')
        ->call('adicionarCartao');

    $colunas = array_keys(Cartao::first()->getAttributes());

    expect($colunas)->not->toContain('numero_cartao')
        ->and($colunas)->not->toContain('cvv');
});

test('segundo cartão cadastrado não vira principal automaticamente', function () {
    $user = User::factory()->create();
    Cartao::factory()->create(['user_id' => $user->id, 'principal' => true]);

    Livewire::actingAs($user)
        ->test(Pagamentos::class)
        ->set('numeroCartao', '5555555555554444')
        ->set('nomeCartao', 'Ana Beatriz Souza')
        ->set('validade', '09/29')
        ->set('cvv', '456')
        ->call('adicionarCartao')
        ->assertHasNoErrors();

    expect(Cartao::count())->toBe(2)
        ->and(Cartao::where('principal', true)->count())->toBe(1);
});

test('dados inválidos do cartão são rejeitados', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Pagamentos::class)
        ->set('numeroCartao', '123')
        ->set('nomeCartao', 'A')
        ->set('validade', '13-30')
        ->set('cvv', '1')
        ->call('adicionarCartao')
        ->assertHasErrors(['numeroCartao', 'nomeCartao', 'validade', 'cvv']);

    expect(Cartao::count())->toBe(0);
});

test('usuário consegue trocar o cartão principal', function () {
    $user = User::factory()->create();
    $antigo = Cartao::factory()->create(['user_id' => $user->id, 'principal' => true]);
    $novo = Cartao::factory()->create(['user_id' => $user->id, 'principal' => false]);

    Livewire::actingAs($user)
        ->test(Pagamentos::class)
        ->call('definirPrincipal', $novo->id)
        ->assertHasNoErrors();

    expect($antigo->fresh()->principal)->toBeFalse()
        ->and($novo->fresh()->principal)->toBeTrue();
});

test('usuário consegue remover um cartão que não é o principal', function () {
    $user = User::factory()->create();
    Cartao::factory()->create(['user_id' => $user->id, 'principal' => true]);
    $secundario = Cartao::factory()->create(['user_id' => $user->id, 'principal' => false]);

    Livewire::actingAs($user)
        ->test(Pagamentos::class)
        ->call('removerCartao', $secundario->id);

    expect(Cartao::count())->toBe(1)
        ->and(Cartao::find($secundario->id))->toBeNull();
});

test('remover o cartão principal promove outro cartão automaticamente', function () {
    $user = User::factory()->create();
    $principal = Cartao::factory()->create(['user_id' => $user->id, 'principal' => true]);
    $outro = Cartao::factory()->create(['user_id' => $user->id, 'principal' => false]);

    Livewire::actingAs($user)
        ->test(Pagamentos::class)
        ->call('removerCartao', $principal->id);

    expect(Cartao::count())->toBe(1)
        ->and($outro->fresh()->principal)->toBeTrue();
});

test('usuário não consegue gerenciar cartão de outro usuário', function () {
    $user = User::factory()->create();
    $outroUsuario = User::factory()->create();
    $cartaoAlheio = Cartao::factory()->create(['user_id' => $outroUsuario->id]);

    expect(fn () => Livewire::actingAs($user)->test(Pagamentos::class)->call('definirPrincipal', $cartaoAlheio->id))
        ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

    expect(fn () => Livewire::actingAs($user)->test(Pagamentos::class)->call('removerCartao', $cartaoAlheio->id))
        ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

    expect($cartaoAlheio->fresh())->not->toBeNull();
});
