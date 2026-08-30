<?php

use App\Enums\UserRole;
use App\Livewire\Admin\Usuarios\Listagem;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Livewire\Livewire;

test('admin vê todos os usuários e pode filtrar por papel', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->create(['name' => 'Jogador Um']);
    User::factory()->donoQuadra()->create(['name' => 'Dono Um']);

    $component = Livewire::actingAs($admin)->test(Listagem::class);

    expect($component->instance()->usuarios)->toHaveCount(3);

    $component->set('filtroRole', UserRole::DonoQuadra->value);

    expect($component->instance()->usuarios)->toHaveCount(1)
        ->and($component->instance()->usuarios->first()->name)->toBe('Dono Um');
});

test('admin troca o papel de outro usuário após confirmar', function () {
    $admin = User::factory()->admin()->create();
    $usuario = User::factory()->create();

    Livewire::actingAs($admin)
        ->test(Listagem::class)
        ->call('prepararTrocaRole', $usuario->id, UserRole::DonoQuadra->value)
        ->call('confirmarTrocaRole');

    expect($usuario->fresh()->role)->toBe(UserRole::DonoQuadra);
});

test('troca de papel gera log de auditoria com admin, usuário e papéis envolvidos', function () {
    $admin = User::factory()->admin()->create();
    $usuario = User::factory()->create();

    Log::spy();

    Livewire::actingAs($admin)
        ->test(Listagem::class)
        ->call('prepararTrocaRole', $usuario->id, UserRole::DonoQuadra->value)
        ->call('confirmarTrocaRole');

    Log::shouldHaveReceived('info')
        ->once()
        ->withArgs(fn (string $mensagem, array $contexto) => $contexto['admin_id'] === $admin->id
            && $contexto['usuario_id'] === $usuario->id
            && $contexto['role_anterior'] === UserRole::Jogador->value
            && $contexto['role_novo'] === UserRole::DonoQuadra->value
        );
});

test('admin não consegue alterar o próprio papel', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Listagem::class)
        ->call('prepararTrocaRole', $admin->id, UserRole::Jogador->value)
        ->assertForbidden();

    expect($admin->fresh()->role)->toBe(UserRole::Admin);
});

test('rota /admin/usuarios exige role admin', function () {
    $admin = User::factory()->admin()->create();
    $jogador = User::factory()->create();

    $this->actingAs($admin)->get(route('admin.usuarios'))->assertOk();
    $this->actingAs($jogador)->get(route('admin.usuarios'))->assertForbidden();
});
