<?php

use App\Enums\ReservaStatus;
use App\Livewire\Perfil\MinhasReservas;
use App\Livewire\Quadras\Pagamento;
use App\Models\Quadra;
use App\Models\Reserva;
use App\Models\User;
use Livewire\Livewire;

test('cancelar reserva pendente não pede tipo e não altera saldo', function () {
    $user = User::factory()->create();
    $quadra = Quadra::factory()->create(['valor_hora' => 80]);
    $reserva = Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'user_id' => $user->id,
        'status' => ReservaStatus::Pendente,
        'data' => now()->addDays(2)->toDateString(),
        'hora_inicio' => '10:00:00',
        'hora_fim' => '11:00:00',
    ]);

    Livewire::actingAs($user)
        ->test(MinhasReservas::class)
        ->call('cancelar', $reserva->id);

    expect($reserva->fresh()->status)->toBe(ReservaStatus::Cancelada);
    expect((float) $user->fresh()->saldo_creditos)->toBe(0.0);
});

test('cancelar reserva confirmada com credito soma o valor da quadra ao saldo', function () {
    $user = User::factory()->create();
    $quadra = Quadra::factory()->create(['valor_hora' => 80]);
    $reserva = Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'user_id' => $user->id,
        'status' => ReservaStatus::Confirmada,
        'data' => now()->addDays(2)->toDateString(),
        'hora_inicio' => '10:00:00',
        'hora_fim' => '11:00:00',
    ]);

    Livewire::actingAs($user)
        ->test(MinhasReservas::class)
        ->call('cancelar', $reserva->id, 'credito');

    expect($reserva->fresh()->status)->toBe(ReservaStatus::Cancelada)
        ->and($reserva->fresh()->cancelamento_tipo)->toBe('credito')
        ->and((float) $user->fresh()->saldo_creditos)->toBe(80.0);
});

test('cancelar reserva confirmada com extorno não altera o saldo', function () {
    $user = User::factory()->create();
    $quadra = Quadra::factory()->create(['valor_hora' => 80]);
    $reserva = Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'user_id' => $user->id,
        'status' => ReservaStatus::Confirmada,
        'data' => now()->addDays(2)->toDateString(),
        'hora_inicio' => '10:00:00',
        'hora_fim' => '11:00:00',
    ]);

    Livewire::actingAs($user)
        ->test(MinhasReservas::class)
        ->call('cancelar', $reserva->id, 'extorno');

    expect($reserva->fresh()->status)->toBe(ReservaStatus::Cancelada)
        ->and($reserva->fresh()->cancelamento_tipo)->toBe('extorno')
        ->and((float) $user->fresh()->saldo_creditos)->toBe(0.0);
});

test('não é possível cancelar reserva de outro usuário', function () {
    $dono = User::factory()->create();
    $outro = User::factory()->create();
    $reserva = Reserva::factory()->create([
        'user_id' => $dono->id,
        'status' => ReservaStatus::Confirmada,
        'data' => now()->addDays(2)->toDateString(),
        'hora_inicio' => '10:00:00',
        'hora_fim' => '11:00:00',
    ]);

    expect(fn () => Livewire::actingAs($outro)
        ->test(MinhasReservas::class)
        ->call('cancelar', $reserva->id, 'credito')
    )->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

    expect($reserva->fresh()->status)->toBe(ReservaStatus::Confirmada);
});

test('não é possível cancelar reserva confirmada a menos de 5h do início', function () {
    $user = User::factory()->create();
    $quadra = Quadra::factory()->create(['valor_hora' => 80]);
    $reserva = Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'user_id' => $user->id,
        'status' => ReservaStatus::Confirmada,
        'data' => now()->toDateString(),
        'hora_inicio' => now()->addHours(2)->format('H:i:s'),
        'hora_fim' => now()->addHours(3)->format('H:i:s'),
    ]);

    Livewire::actingAs($user)
        ->test(MinhasReservas::class)
        ->call('cancelar', $reserva->id, 'credito');

    expect($reserva->fresh()->status)->toBe(ReservaStatus::Confirmada);
    expect((float) $user->fresh()->saldo_creditos)->toBe(0.0);
});

test('tipo de cancelamento inválido é rejeitado', function () {
    $user = User::factory()->create();
    $quadra = Quadra::factory()->create(['valor_hora' => 80]);
    $reserva = Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'user_id' => $user->id,
        'status' => ReservaStatus::Confirmada,
        'data' => now()->addDays(2)->toDateString(),
        'hora_inicio' => '10:00:00',
        'hora_fim' => '11:00:00',
    ]);

    Livewire::actingAs($user)
        ->test(MinhasReservas::class)
        ->call('cancelar', $reserva->id, 'roubar');

    expect($reserva->fresh()->status)->toBe(ReservaStatus::Confirmada);
});

test('pagar com credito confirma a reserva e desconta o saldo quando suficiente', function () {
    $user = User::factory()->create();
    $user->saldo_creditos = 100;
    $user->save();

    $quadra = Quadra::factory()->create(['valor_hora' => 80]);
    $reserva = Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'user_id' => $user->id,
        'status' => ReservaStatus::Pendente,
    ]);

    Livewire::actingAs($user)
        ->test(Pagamento::class, ['reserva' => $reserva])
        ->call('pagarComCredito')
        ->assertRedirect(route('reservas.confirmacao', $reserva));

    expect($reserva->fresh()->status)->toBe(ReservaStatus::Confirmada)
        ->and($reserva->fresh()->metodo_pagamento)->toBe('credito')
        ->and((float) $user->fresh()->saldo_creditos)->toBe(20.0);
});

test('pagar com credito é rejeitado quando o saldo é insuficiente', function () {
    $user = User::factory()->create();
    $user->saldo_creditos = 10;
    $user->save();

    $quadra = Quadra::factory()->create(['valor_hora' => 80]);
    $reserva = Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'user_id' => $user->id,
        'status' => ReservaStatus::Pendente,
    ]);

    Livewire::actingAs($user)
        ->test(Pagamento::class, ['reserva' => $reserva])
        ->call('pagarComCredito')
        ->assertForbidden();

    expect($reserva->fresh()->status)->toBe(ReservaStatus::Pendente);
    expect((float) $user->fresh()->saldo_creditos)->toBe(10.0);
});
