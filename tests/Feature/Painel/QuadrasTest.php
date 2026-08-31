<?php

use App\Enums\ReservaStatus;
use App\Livewire\Painel\Quadras\Listagem;
use App\Models\Quadra;
use App\Models\Reserva;
use App\Models\User;
use Livewire\Livewire;

test('rota painel.quadras renderiza a listagem de quadras do dono', function () {
    $dono = User::factory()->donoQuadra()->create();

    $this->actingAs($dono)
        ->get(route('painel.quadras'))
        ->assertOk()
        ->assertSeeLivewire(Listagem::class);
});

test('rota painel.dashboard não renderiza mais a listagem de quadras', function () {
    $dono = User::factory()->donoQuadra()->create();

    $this->actingAs($dono)
        ->get(route('painel.dashboard'))
        ->assertOk()
        ->assertDontSeeLivewire(Listagem::class);
});

test('dono vê apenas as próprias quadras na listagem', function () {
    $dono = User::factory()->donoQuadra()->create();
    $outroDono = User::factory()->donoQuadra()->create();

    Quadra::factory()->create(['dono_id' => $dono->id, 'nome' => 'Quadra do Dono']);
    Quadra::factory()->create(['dono_id' => $outroDono->id, 'nome' => 'Quadra de Outro Dono']);

    Livewire::actingAs($dono)
        ->test(Listagem::class)
        ->assertSee('Quadra do Dono')
        ->assertDontSee('Quadra de Outro Dono');
});

test('dono não consegue excluir quadra de outro dono', function () {
    $dono = User::factory()->donoQuadra()->create();
    $outroDono = User::factory()->donoQuadra()->create();
    $quadraAlheia = Quadra::factory()->create(['dono_id' => $outroDono->id]);

    Livewire::actingAs($dono)
        ->test(Listagem::class)
        ->call('pedirExclusao', $quadraAlheia->id)
        ->assertForbidden();

    expect(Quadra::count())->toBe(1);
});

test('exclusão é bloqueada quando a quadra tem reservas futuras não canceladas', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id]);

    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => now()->addDay()->toDateString(),
        'status' => ReservaStatus::Confirmada,
    ]);

    $component = Livewire::actingAs($dono)
        ->test(Listagem::class)
        ->call('pedirExclusao', $quadra->id)
        ->call('excluir');

    expect($component->get('bloqueioExclusao'))->not->toBeNull();
    expect(Quadra::query()->find($quadra->id))->not->toBeNull();
});

test('exclusão funciona quando a quadra não tem reservas futuras pendentes', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id]);

    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => now()->subDay()->toDateString(),
        'status' => ReservaStatus::Confirmada,
    ]);

    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => now()->addDay()->toDateString(),
        'status' => ReservaStatus::Cancelada,
    ]);

    Livewire::actingAs($dono)
        ->test(Listagem::class)
        ->call('pedirExclusao', $quadra->id)
        ->call('excluir');

    expect(Quadra::query()->find($quadra->id))->toBeNull();
});

test('busca filtra a listagem pelo nome da quadra', function () {
    $dono = User::factory()->donoQuadra()->create();

    Quadra::factory()->create(['dono_id' => $dono->id, 'nome' => 'Arena Central']);
    Quadra::factory()->create(['dono_id' => $dono->id, 'nome' => 'Quadra do Parque']);

    Livewire::actingAs($dono)
        ->test(Listagem::class)
        ->set('busca', 'Central')
        ->assertSee('Arena Central')
        ->assertDontSee('Quadra do Parque');
});

test('filtro de status mostra apenas quadras ativas ou inativas', function () {
    $dono = User::factory()->donoQuadra()->create();

    Quadra::factory()->create(['dono_id' => $dono->id, 'nome' => 'Quadra Ativa', 'ativa' => true]);
    Quadra::factory()->create(['dono_id' => $dono->id, 'nome' => 'Quadra Inativa', 'ativa' => false]);

    $component = Livewire::actingAs($dono)->test(Listagem::class);

    $component->set('filtroStatus', 'ativa')
        ->assertSee('Quadra Ativa')
        ->assertDontSee('Quadra Inativa');

    $component->set('filtroStatus', 'inativa')
        ->assertSee('Quadra Inativa')
        ->assertDontSee('Quadra Ativa');

    $component->set('filtroStatus', 'todas')
        ->assertSee('Quadra Ativa')
        ->assertSee('Quadra Inativa');
});

test('dono cancela uma quadra ativa', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id, 'ativa' => true]);

    Livewire::actingAs($dono)
        ->test(Listagem::class)
        ->call('cancelar', $quadra->id);

    expect($quadra->fresh()->ativa)->toBeFalse();
});

test('dono reativa uma quadra cancelada', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id, 'ativa' => false]);

    Livewire::actingAs($dono)
        ->test(Listagem::class)
        ->call('ativar', $quadra->id);

    expect($quadra->fresh()->ativa)->toBeTrue();
});

test('dono não consegue cancelar nem ativar quadra de outro dono', function () {
    $dono = User::factory()->donoQuadra()->create();
    $outroDono = User::factory()->donoQuadra()->create();
    $quadraAlheia = Quadra::factory()->create(['dono_id' => $outroDono->id, 'ativa' => true]);

    Livewire::actingAs($dono)
        ->test(Listagem::class)
        ->call('cancelar', $quadraAlheia->id)
        ->assertForbidden();

    Livewire::actingAs($dono)
        ->test(Listagem::class)
        ->call('ativar', $quadraAlheia->id)
        ->assertForbidden();

    expect($quadraAlheia->fresh()->ativa)->toBeTrue();
});
