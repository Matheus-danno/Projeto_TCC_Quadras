<?php

use App\Enums\ReservaStatus;
use App\Livewire\Admin\Dashboard;
use App\Models\Quadra;
use App\Models\Reserva;
use App\Models\User;
use Livewire\Livewire;

test('dashboard do admin mostra os indicadores corretos', function () {
    $admin = User::factory()->admin()->create();

    User::factory()->count(3)->create();
    User::factory()->donoQuadra()->count(2)->create();

    $jogador = User::factory()->create();

    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id]);
    Quadra::factory()->count(2)->create(['dono_id' => $dono->id]);

    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'user_id' => $jogador->id,
        'status' => ReservaStatus::Confirmada,
        'data' => now()->startOfMonth()->addDays(2)->toDateString(),
    ]);
    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'user_id' => $jogador->id,
        'status' => ReservaStatus::Confirmada,
        'data' => now()->subMonthNoOverflow()->toDateString(),
    ]);
    Reserva::factory()->count(2)->create([
        'quadra_id' => $quadra->id,
        'user_id' => $jogador->id,
        'status' => ReservaStatus::Pendente,
        'data' => now()->addDay()->toDateString(),
    ]);

    $indicadores = Livewire::actingAs($admin)
        ->test(Dashboard::class)
        ->instance()
        ->indicadores;

    expect($indicadores['jogadores'])->toBe(4)
        ->and($indicadores['donosQuadra'])->toBe(3)
        ->and($indicadores['admins'])->toBe(1)
        ->and($indicadores['quadras'])->toBe(3)
        ->and($indicadores['reservasConfirmadasMes'])->toBe(1)
        ->and($indicadores['reservasPendentes'])->toBe(2);
});

test('rota /admin exige role admin', function () {
    $admin = User::factory()->admin()->create();
    $jogador = User::factory()->create();

    $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
    $this->actingAs($jogador)->get(route('admin.dashboard'))->assertForbidden();
});
