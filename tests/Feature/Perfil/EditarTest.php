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
