<?php

use App\Livewire\Perfil\Editar;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('usuário consegue atualizar nome e e-mail', function () {
    $user = User::factory()->create(['name' => 'Nome Antigo', 'email' => 'antigo@demo.com']);

    Livewire::actingAs($user)
        ->test(Editar::class)
        ->set('name', 'Nome Novo')
        ->set('email', 'novo@demo.com')
        ->call('salvar')
        ->assertHasNoErrors();

    expect($user->fresh()->name)->toBe('Nome Novo')
        ->and($user->fresh()->email)->toBe('novo@demo.com')
        ->and($user->fresh()->email_verified_at)->toBeNull();
});

test('formulário vem preenchido com todos os dados cadastrados no registro', function () {
    $user = User::factory()->create([
        'cpf' => '12345678901',
        'data_nascimento' => '1990-05-20',
        'sexo' => 'masculino',
        'endereco' => 'Rua das Flores, 100',
        'cep' => '17010000',
        'cidade' => 'Bauru',
        'estado' => 'SP',
        'telefone' => '14991234567',
    ]);

    Livewire::actingAs($user)
        ->test(Editar::class)
        ->assertSet('dataNascimento', '1990-05-20')
        ->assertSet('sexo', 'masculino')
        ->assertSet('endereco', 'Rua das Flores, 100')
        ->assertSet('cep', '17010-000')
        ->assertSet('cidade', 'Bauru')
        ->assertSet('estado', 'SP')
        ->assertSet('telefone', '(14) 99123-4567')
        ->assertSee('123.456.789-01');
});

test('usuário consegue atualizar endereço, contato e dados pessoais', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Editar::class)
        ->set('dataNascimento', '1995-03-10')
        ->set('sexo', 'feminino')
        ->set('endereco', 'Av. Nova, 200')
        ->set('cep', '17012-345')
        ->set('cidade', 'Bauru')
        ->set('estado', 'sp')
        ->set('telefone', '(14) 98888-7777')
        ->call('salvar')
        ->assertHasNoErrors();

    $user->refresh();

    expect($user->data_nascimento->toDateString())->toBe('1995-03-10')
        ->and($user->sexo->value)->toBe('feminino')
        ->and($user->endereco)->toBe('Av. Nova, 200')
        ->and($user->cep)->toBe('17012345')
        ->and($user->cidade)->toBe('Bauru')
        ->and($user->estado)->toBe('SP')
        ->and($user->telefone)->toBe('14988887777');
});

test('cpf não pode ser alterado pelo formulário de editar perfil', function () {
    $user = User::factory()->create(['cpf' => '12345678901']);

    expect(fn () => Livewire::actingAs($user)->test(Editar::class)->set('cpf', '99999999999'))
        ->toThrow(\Livewire\Exceptions\PublicPropertyNotFoundException::class);

    expect($user->fresh()->cpf)->toBe('12345678901');
});

test('e-mail já usado por outro usuário é rejeitado', function () {
    User::factory()->create(['email' => 'ocupado@demo.com']);
    $user = User::factory()->create(['email' => 'meu@demo.com']);

    Livewire::actingAs($user)
        ->test(Editar::class)
        ->set('email', 'ocupado@demo.com')
        ->call('salvar')
        ->assertHasErrors('email');
});

test('usuário consegue enviar uma foto de perfil', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Editar::class)
        ->set('avatar', UploadedFile::fake()->image('avatar.jpg'))
        ->call('salvarFoto')
        ->assertHasNoErrors();

    $user->refresh();

    expect($user->avatar_path)->not->toBeNull();

    Storage::disk('public')->assertExists($user->avatar_path);
    expect($user->avatarUrl())->toContain($user->avatar_path);
});

test('enviar uma nova foto substitui e apaga a anterior', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Editar::class)
        ->set('avatar', UploadedFile::fake()->image('primeira.jpg'))
        ->call('salvarFoto');

    $caminhoAntigo = $user->fresh()->avatar_path;

    Livewire::actingAs($user->fresh())
        ->test(Editar::class)
        ->set('avatar', UploadedFile::fake()->image('segunda.jpg'))
        ->call('salvarFoto');

    $user->refresh();

    Storage::disk('public')->assertMissing($caminhoAntigo);
    Storage::disk('public')->assertExists($user->avatar_path);
    expect($user->avatar_path)->not->toBe($caminhoAntigo);
});

test('usuário consegue remover a foto de perfil enviada', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Editar::class)
        ->set('avatar', UploadedFile::fake()->image('avatar.jpg'))
        ->call('salvarFoto');

    $caminho = $user->fresh()->avatar_path;

    Livewire::actingAs($user->fresh())
        ->test(Editar::class)
        ->call('removerFoto');

    Storage::disk('public')->assertMissing($caminho);
    expect($user->fresh()->avatar_path)->toBeNull();
});

test('arquivo que não é imagem é rejeitado ao enviar a foto', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Editar::class)
        ->set('avatar', UploadedFile::fake()->create('documento.pdf', 100))
        ->call('salvarFoto')
        ->assertHasErrors('avatar');

    expect($user->fresh()->avatar_path)->toBeNull();
});

test('usuário sem foto enviada usa o avatar gerado a partir do e-mail', function () {
    $user = User::factory()->create(['email' => 'sememail@demo.com']);

    expect($user->avatarUrl())->toContain('pravatar.cc')
        ->and($user->avatarUrl())->toContain('sememail%40demo.com');
});
