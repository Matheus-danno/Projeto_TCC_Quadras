<?php

use App\Enums\StatusAprovacaoQuadra;
use App\Livewire\Admin\Quadras\Listagem;
use App\Livewire\Quadras\Listagem as ListagemPublica;
use App\Models\Quadra;
use App\Models\User;
use Livewire\Livewire;

test('quadra nova nasce aprovada por padrão, sem precisar de moderação', function () {
    $quadra = Quadra::factory()->create();

    expect($quadra->refresh()->status_aprovacao)->toBe(StatusAprovacaoQuadra::Aprovada);
});

test('admin consegue suspender uma quadra aprovada e ela some da listagem pública', function () {
    $admin = User::factory()->admin()->create();
    $quadra = Quadra::factory()->create(['ativa' => true]);

    Livewire::actingAs($admin)->test(Listagem::class)
        ->call('suspender', $quadra->id);

    expect($quadra->refresh()->status_aprovacao)->toBe(StatusAprovacaoQuadra::Suspensa);

    Livewire::test(ListagemPublica::class)
        ->assertDontSee($quadra->nome);
});

test('admin consegue reaprovar uma quadra suspensa e ela volta a aparecer na listagem pública', function () {
    $admin = User::factory()->admin()->create();
    $quadra = Quadra::factory()->create(['status_aprovacao' => StatusAprovacaoQuadra::Suspensa]);

    Livewire::actingAs($admin)->test(Listagem::class)
        ->call('aprovar', $quadra->id);

    expect($quadra->refresh()->status_aprovacao)->toBe(StatusAprovacaoQuadra::Aprovada);

    Livewire::test(ListagemPublica::class)
        ->assertSee($quadra->nome);
});

test('dono de quadra não pode aprovar ou suspender quadras', function () {
    $dono = User::factory()->donoQuadra()->create();

    $this->actingAs($dono)->get(route('admin.quadras'))->assertForbidden();
});
