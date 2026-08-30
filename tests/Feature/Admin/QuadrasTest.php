<?php

use App\Enums\Esporte;
use App\Enums\ReservaStatus;
use App\Livewire\Admin\Quadras\Listagem;
use App\Models\Quadra;
use App\Models\Reserva;
use App\Models\User;
use Livewire\Livewire;

test('admin vê quadras de todos os donos', function () {
    $admin = User::factory()->admin()->create();
    $donoA = User::factory()->donoQuadra()->create();
    $donoB = User::factory()->donoQuadra()->create();

    Quadra::factory()->create(['dono_id' => $donoA->id, 'nome' => 'Quadra do Dono A']);
    Quadra::factory()->create(['dono_id' => $donoB->id, 'nome' => 'Quadra do Dono B']);

    Livewire::actingAs($admin)
        ->test(Listagem::class)
        ->assertSee('Quadra do Dono A')
        ->assertSee('Quadra do Dono B');
});

test('filtros por cidade, esporte e dono funcionam', function () {
    $admin = User::factory()->admin()->create();
    $donoA = User::factory()->donoQuadra()->create();
    $donoB = User::factory()->donoQuadra()->create();

    $quadraA = Quadra::factory()->create([
        'dono_id' => $donoA->id,
        'cidade' => 'Recife',
        'esporte' => Esporte::Futsal,
    ]);
    Quadra::factory()->create([
        'dono_id' => $donoB->id,
        'cidade' => 'Olinda',
        'esporte' => Esporte::Volei,
    ]);

    $component = Livewire::actingAs($admin)->test(Listagem::class);

    expect($component->set('filtroCidade', 'Recife')->instance()->quadras)->toHaveCount(1);

    $component->set('filtroCidade', '');
    expect($component->set('filtroEsporte', Esporte::Futsal->value)->instance()->quadras)->toHaveCount(1);

    $component->set('filtroEsporte', '');
    expect($component->set('filtroDonoId', $donoA->id)->instance()->quadras->first()->id)->toBe($quadraA->id);
});

test('admin edita qualquer quadra sem alterar o dono', function () {
    $admin = User::factory()->admin()->create();
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id, 'nome' => 'Nome Antigo']);

    Livewire::actingAs($admin)
        ->test(Listagem::class)
        ->call('editar', $quadra->id)
        ->set('nome', 'Nome Editado pelo Admin')
        ->call('salvar')
        ->assertHasNoErrors();

    expect($quadra->fresh()->nome)->toBe('Nome Editado pelo Admin')
        ->and($quadra->fresh()->dono_id)->toBe($dono->id);
});

test('admin não consegue excluir quadra com reservas futuras', function () {
    $admin = User::factory()->admin()->create();
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id]);

    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'status' => ReservaStatus::Confirmada,
        'data' => now()->addDay()->toDateString(),
    ]);

    $component = Livewire::actingAs($admin)
        ->test(Listagem::class)
        ->call('pedirExclusao', $quadra->id)
        ->call('excluir');

    expect($component->get('bloqueioExclusao'))->not->toBeNull();
    expect(Quadra::query()->find($quadra->id))->not->toBeNull();
});

test('rota /admin/quadras exige role admin', function () {
    $admin = User::factory()->admin()->create();
    $dono = User::factory()->donoQuadra()->create();

    $this->actingAs($admin)->get(route('admin.quadras'))->assertOk();
    $this->actingAs($dono)->get(route('admin.quadras'))->assertForbidden();
});
