<?php

use App\Models\Quadra;
use App\Models\User;
use App\Policies\QuadraPolicy;

test('dono pode atualizar e excluir a própria quadra', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id]);

    $policy = new QuadraPolicy;

    expect($policy->update($dono, $quadra))->toBeTrue()
        ->and($policy->delete($dono, $quadra))->toBeTrue();
});

test('dono não pode atualizar nem excluir quadra de outro dono', function () {
    $dono = User::factory()->donoQuadra()->create();
    $outroDono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $outroDono->id]);

    $policy = new QuadraPolicy;

    expect($policy->update($dono, $quadra))->toBeFalse()
        ->and($policy->delete($dono, $quadra))->toBeFalse();
});

test('admin pode atualizar e excluir qualquer quadra', function () {
    $admin = User::factory()->admin()->create();
    $quadra = Quadra::factory()->create();

    $policy = new QuadraPolicy;

    expect($policy->update($admin, $quadra))->toBeTrue()
        ->and($policy->delete($admin, $quadra))->toBeTrue();
});

test('jogador não pode atualizar nem excluir nenhuma quadra', function () {
    $jogador = User::factory()->create();
    $quadra = Quadra::factory()->create();

    $policy = new QuadraPolicy;

    expect($policy->update($jogador, $quadra))->toBeFalse()
        ->and($policy->delete($jogador, $quadra))->toBeFalse();
});
