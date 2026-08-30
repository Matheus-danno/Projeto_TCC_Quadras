<?php

use App\Enums\ReservaStatus;
use App\Livewire\Quadras\Listagem;
use App\Models\Quadra;
use App\Models\Reserva;
use App\Models\User;
use Livewire\Livewire;

test('listagem de quadras filtra por cidade', function () {
    Quadra::factory()->create(['nome' => 'Quadra Recife Centro', 'cidade' => 'Recife']);
    Quadra::factory()->create(['nome' => 'Quadra Olinda Praia', 'cidade' => 'Olinda']);

    $component = Livewire::test(Listagem::class)->set('cidade', 'Recife');

    $component->assertSee('Quadra Recife Centro');

    expect($component->instance()->quadras->pluck('nome')->all())
        ->toBe(['Quadra Recife Centro']);
});

test('listagem de quadras filtra por nome da quadra', function () {
    Quadra::factory()->create(['nome' => 'Quadra Recife Centro']);
    Quadra::factory()->create(['nome' => 'Quadra Olinda Praia']);

    $component = Livewire::test(Listagem::class)->set('quadraNome', 'Quadra Recife Centro');

    expect($component->instance()->quadras->pluck('nome')->all())
        ->toBe(['Quadra Recife Centro']);
});

test('listagem de quadras filtra por cobertura', function () {
    Quadra::factory()->create(['nome' => 'Quadra Coberta', 'cobertura' => true]);
    Quadra::factory()->create(['nome' => 'Quadra Descoberta', 'cobertura' => false]);

    $coberta = Livewire::test(Listagem::class)->set('cobertura', '1');
    expect($coberta->instance()->quadras->pluck('nome')->all())->toBe(['Quadra Coberta']);

    $descoberta = Livewire::test(Listagem::class)->set('cobertura', '0');
    expect($descoberta->instance()->quadras->pluck('nome')->all())->toBe(['Quadra Descoberta']);
});

test('usuário autenticado consegue reservar um horário disponível', function () {
    $user = User::factory()->create();
    $quadra = Quadra::factory()->create();
    $data = now()->addDay()->toDateString();

    Livewire::actingAs($user)
        ->test(Listagem::class)
        ->call('selecionarQuadra', $quadra->id)
        ->set('data', $data)
        ->set('horaInicio', '10:00')
        ->call('reservar')
        ->assertHasNoErrors();

    expect(Reserva::count())->toBe(1);

    $reserva = Reserva::first();

    expect($reserva->quadra_id)->toBe($quadra->id)
        ->and($reserva->user_id)->toBe($user->id)
        ->and($reserva->status)->toBe(ReservaStatus::Confirmada)
        ->and($reserva->hora_inicio)->toBe('10:00:00')
        ->and($reserva->hora_fim)->toBe('11:00:00');
});

test('reserva em horário já ocupado é rejeitada', function () {
    $user = User::factory()->create();
    $quadra = Quadra::factory()->create();
    $data = now()->addDay()->toDateString();

    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => $data,
        'hora_inicio' => '10:00:00',
        'hora_fim' => '11:00:00',
        'status' => ReservaStatus::Confirmada,
    ]);

    Livewire::actingAs($user)
        ->test(Listagem::class)
        ->call('selecionarQuadra', $quadra->id)
        ->set('data', $data)
        ->set('horaInicio', '10:00')
        ->call('reservar')
        ->assertHasErrors('horaInicio');

    expect(Reserva::count())->toBe(1);
});
