<?php

use App\Enums\ReservaStatus;
use App\Livewire\Painel\Reservas\Agenda;
use App\Models\Quadra;
use App\Models\Reserva;
use App\Models\User;
use Livewire\Livewire;

test('rota painel.reservas.agenda renderiza a agenda do dono', function () {
    $dono = User::factory()->donoQuadra()->create();

    $this->actingAs($dono)
        ->get(route('painel.reservas.agenda'))
        ->assertOk()
        ->assertSeeLivewire(Agenda::class);
});

test('semanas marca os dias com reservas confirmadas e pendentes', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id]);

    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => now()->toDateString(),
        'status' => ReservaStatus::Confirmada,
    ]);

    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => now()->addDay()->toDateString(),
        'status' => ReservaStatus::Pendente,
    ]);

    $semanas = Livewire::actingAs($dono)
        ->test(Agenda::class)
        ->instance()
        ->semanas;

    $dias = collect($semanas)->flatten(1)->keyBy(fn (array $dia) => $dia['data']->toDateString());

    expect($dias[now()->toDateString()]['confirmadas'])->toBe(1)
        ->and($dias[now()->addDay()->toDateString()]['pendentes'])->toBe(1);
});

test('reservas canceladas não aparecem na agenda', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id]);

    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => now()->toDateString(),
        'status' => ReservaStatus::Cancelada,
    ]);

    $semanas = Livewire::actingAs($dono)
        ->test(Agenda::class)
        ->instance()
        ->semanas;

    $dias = collect($semanas)->flatten(1)->keyBy(fn (array $dia) => $dia['data']->toDateString());

    expect($dias[now()->toDateString()]['confirmadas'])->toBe(0)
        ->and($dias[now()->toDateString()]['pendentes'])->toBe(0);
});

test('reservasDoDia lista as reservas do dia selecionado, sem misturar outros dias', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id, 'nome' => 'Quadra Teste']);

    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => now()->toDateString(),
        'status' => ReservaStatus::Confirmada,
    ]);

    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => now()->addDay()->toDateString(),
        'status' => ReservaStatus::Confirmada,
    ]);

    $reservas = Livewire::actingAs($dono)
        ->test(Agenda::class)
        ->call('selecionarDia', now()->toDateString())
        ->instance()
        ->reservasDoDia;

    expect($reservas)->toHaveCount(1)
        ->and($reservas->first()->quadra->nome)->toBe('Quadra Teste');
});

test('horariosDoDia marca como ocupados apenas os horários com reserva da quadra selecionada', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id]);
    $outraQuadra = Quadra::factory()->create(['dono_id' => $dono->id]);

    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => now()->toDateString(),
        'hora_inicio' => '19:00:00',
        'hora_fim' => '20:00:00',
        'status' => ReservaStatus::Confirmada,
    ]);

    Reserva::factory()->create([
        'quadra_id' => $outraQuadra->id,
        'data' => now()->toDateString(),
        'hora_inicio' => '10:00:00',
        'hora_fim' => '11:00:00',
        'status' => ReservaStatus::Confirmada,
    ]);

    $horarios = Livewire::actingAs($dono)
        ->test(Agenda::class)
        ->set('quadraId', (string) $quadra->id)
        ->call('selecionarDia', now()->toDateString())
        ->instance()
        ->horariosDoDia;

    $porInicio = collect($horarios)->keyBy('inicio');

    expect($porInicio['19:00']['ocupado'])->toBeTrue()
        ->and($porInicio['10:00']['ocupado'])->toBeFalse();
});

test('mudarMes navega para o mes seguinte e anterior', function () {
    $dono = User::factory()->donoQuadra()->create();

    $component = Livewire::actingAs($dono)->test(Agenda::class);

    $mesInicial = \Illuminate\Support\Carbon::parse($component->get('mesAtual'));

    $component->call('mudarMes', 1);
    expect(\Illuminate\Support\Carbon::parse($component->get('mesAtual')))
        ->toEqual($mesInicial->copy()->addMonth());

    $component->call('mudarMes', -1);
    expect(\Illuminate\Support\Carbon::parse($component->get('mesAtual')))
        ->toEqual($mesInicial);
});

test('dono vê apenas as próprias quadras na agenda', function () {
    $dono = User::factory()->donoQuadra()->create();
    $outroDono = User::factory()->donoQuadra()->create();

    Quadra::factory()->create(['dono_id' => $dono->id, 'nome' => 'Quadra do Dono']);
    Quadra::factory()->create(['dono_id' => $outroDono->id, 'nome' => 'Quadra de Outro Dono']);

    Livewire::actingAs($dono)
        ->test(Agenda::class)
        ->assertSee('Quadra do Dono')
        ->assertDontSee('Quadra de Outro Dono');
});
