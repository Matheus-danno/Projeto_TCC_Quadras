<?php

use App\Models\Quadra;
use App\Models\Reserva;
use App\Models\User;
use App\Policies\ReservaPolicy;

test('dono pode atualizar reserva de uma quadra própria', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id]);
    $reserva = Reserva::factory()->create(['quadra_id' => $quadra->id]);

    expect((new ReservaPolicy)->update($dono, $reserva))->toBeTrue();
});

test('dono não pode atualizar reserva de quadra que não é dele', function () {
    $dono = User::factory()->donoQuadra()->create();
    $outroDono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $outroDono->id]);
    $reserva = Reserva::factory()->create(['quadra_id' => $quadra->id]);

    expect((new ReservaPolicy)->update($dono, $reserva))->toBeFalse();
});

test('jogador não pode atualizar reserva de nenhuma quadra', function () {
    $jogador = User::factory()->create();
    $reserva = Reserva::factory()->create();

    expect((new ReservaPolicy)->update($jogador, $reserva))->toBeFalse();
});
