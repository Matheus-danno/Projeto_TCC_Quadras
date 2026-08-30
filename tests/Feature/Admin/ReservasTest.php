<?php

use App\Enums\ReservaStatus;
use App\Livewire\Admin\Reservas\Listagem;
use App\Models\Quadra;
use App\Models\Reserva;
use App\Models\User;
use Livewire\Livewire;

test('admin vê reservas de todos os donos e pode filtrar por quadra, dono e status', function () {
    $admin = User::factory()->admin()->create();
    $donoA = User::factory()->donoQuadra()->create();
    $donoB = User::factory()->donoQuadra()->create();

    $quadraA = Quadra::factory()->create(['dono_id' => $donoA->id]);
    $quadraB = Quadra::factory()->create(['dono_id' => $donoB->id]);

    $reservaA = Reserva::factory()->create(['quadra_id' => $quadraA->id, 'status' => ReservaStatus::Pendente]);
    Reserva::factory()->create(['quadra_id' => $quadraB->id, 'status' => ReservaStatus::Confirmada]);

    $component = Livewire::actingAs($admin)->test(Listagem::class);

    expect($component->instance()->reservas)->toHaveCount(2);

    $component->set('quadraId', $quadraA->id);
    expect($component->instance()->reservas)->toHaveCount(1)
        ->and($component->instance()->reservas->first()->id)->toBe($reservaA->id);

    $component->set('quadraId', '');
    $component->set('donoId', $donoB->id);
    expect($component->instance()->reservas)->toHaveCount(1)
        ->and($component->instance()->reservas->first()->quadra->dono_id)->toBe($donoB->id);

    $component->set('donoId', '');
    $component->set('status', ReservaStatus::Confirmada->value);
    expect($component->instance()->reservas)->toHaveCount(1)
        ->and($component->instance()->reservas->first()->status)->toBe(ReservaStatus::Confirmada);
});

test('filtro por período (data inicial e final) funciona', function () {
    $admin = User::factory()->admin()->create();
    $quadra = Quadra::factory()->create();

    $dentro = Reserva::factory()->create(['quadra_id' => $quadra->id, 'data' => '2026-08-10']);
    Reserva::factory()->create(['quadra_id' => $quadra->id, 'data' => '2026-07-01']);
    Reserva::factory()->create(['quadra_id' => $quadra->id, 'data' => '2026-09-01']);

    $reservas = Livewire::actingAs($admin)
        ->test(Listagem::class)
        ->set('dataInicio', '2026-08-01')
        ->set('dataFim', '2026-08-31')
        ->instance()
        ->reservas;

    expect($reservas)->toHaveCount(1)
        ->and($reservas->first()->id)->toBe($dentro->id);
});

test('resumo soma quantidade e valor das reservas confirmadas no período filtrado', function () {
    $admin = User::factory()->admin()->create();
    $quadra = Quadra::factory()->create(['valor_hora' => 100]);
    $outraQuadra = Quadra::factory()->create(['valor_hora' => 50]);

    Reserva::factory()->create(['quadra_id' => $quadra->id, 'status' => ReservaStatus::Confirmada, 'data' => '2026-08-05']);
    Reserva::factory()->create(['quadra_id' => $outraQuadra->id, 'status' => ReservaStatus::Confirmada, 'data' => '2026-08-06']);
    Reserva::factory()->create(['quadra_id' => $quadra->id, 'status' => ReservaStatus::Pendente, 'data' => '2026-08-07']);
    Reserva::factory()->create(['quadra_id' => $quadra->id, 'status' => ReservaStatus::Cancelada, 'data' => '2026-08-08']);

    $resumo = Livewire::actingAs($admin)
        ->test(Listagem::class)
        ->set('dataInicio', '2026-08-01')
        ->set('dataFim', '2026-08-31')
        ->instance()
        ->resumo;

    expect($resumo['quantidade'])->toBe(4)
        ->and($resumo['valorTotalConfirmadas'])->toBe(150.0);
});

test('admin confirma e cancela qualquer reserva da plataforma', function () {
    $admin = User::factory()->admin()->create();
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id]);
    $reserva = Reserva::factory()->create(['quadra_id' => $quadra->id, 'status' => ReservaStatus::Pendente]);

    Livewire::actingAs($admin)
        ->test(Listagem::class)
        ->call('confirmar', $reserva->id);

    expect($reserva->fresh()->status)->toBe(ReservaStatus::Confirmada);

    Livewire::actingAs($admin)
        ->test(Listagem::class)
        ->call('cancelar', $reserva->id);

    expect($reserva->fresh()->status)->toBe(ReservaStatus::Cancelada);
});

test('rota /admin/reservas exige role admin', function () {
    $admin = User::factory()->admin()->create();
    $dono = User::factory()->donoQuadra()->create();

    $this->actingAs($admin)->get(route('admin.reservas'))->assertOk();
    $this->actingAs($dono)->get(route('admin.reservas'))->assertForbidden();
});
