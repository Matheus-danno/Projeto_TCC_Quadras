<?php

use App\Enums\ReservaStatus;
use App\Livewire\Painel\Dashboard;
use App\Models\Quadra;
use App\Models\Reserva;
use App\Models\User;
use Livewire\Livewire;

test('indicadores contam apenas quadras e reservas do próprio dono', function () {
    $dono = User::factory()->donoQuadra()->create();
    $outroDono = User::factory()->donoQuadra()->create();

    $quadra = Quadra::factory()->create(['dono_id' => $dono->id, 'valor_hora' => 100]);
    Quadra::factory()->create(['dono_id' => $dono->id, 'valor_hora' => 50]);
    $quadraOutroDono = Quadra::factory()->create(['dono_id' => $outroDono->id]);

    // Reserva confirmada de 2h no mês atual: entra na contagem e no faturamento (100 * 2 = 200).
    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => now()->startOfMonth()->addDays(2)->toDateString(),
        'hora_inicio' => '08:00:00',
        'hora_fim' => '10:00:00',
        'status' => ReservaStatus::Confirmada,
    ]);

    // Reserva pendente no mês atual: entra na contagem, mas não no faturamento.
    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => now()->startOfMonth()->addDays(3)->toDateString(),
        'hora_inicio' => '08:00:00',
        'hora_fim' => '09:00:00',
        'status' => ReservaStatus::Pendente,
    ]);

    // Reserva confirmada de outro dono no mesmo mês: não deve contar.
    Reserva::factory()->create([
        'quadra_id' => $quadraOutroDono->id,
        'data' => now()->startOfMonth()->addDays(2)->toDateString(),
        'status' => ReservaStatus::Confirmada,
    ]);

    // Reserva confirmada do dono, mas em mês passado: não deve contar no faturamento nem na contagem do mês.
    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => now()->subMonth()->startOfMonth()->addDays(5)->toDateString(),
        'hora_inicio' => '08:00:00',
        'hora_fim' => '12:00:00',
        'status' => ReservaStatus::Confirmada,
    ]);

    $indicadores = Livewire::actingAs($dono)
        ->test(Dashboard::class)
        ->instance()
        ->indicadores;

    expect($indicadores['quantidadeQuadras'])->toBe(2)
        ->and($indicadores['reservasDoMes'])->toBe(2)
        ->and((float) $indicadores['faturamentoDoMes'])->toBe(200.0)
        ->and($indicadores['avaliacaoMedia'])->toBe('—');
});

test('prévia de quadras mostra até 5 quadras do dono, ordenadas por nome, com contagem de reservas', function () {
    $dono = User::factory()->donoQuadra()->create();

    $quadras = Quadra::factory()
        ->count(6)
        ->sequence(
            ['nome' => 'Quadra A'],
            ['nome' => 'Quadra B'],
            ['nome' => 'Quadra C'],
            ['nome' => 'Quadra D'],
            ['nome' => 'Quadra E'],
            ['nome' => 'Quadra F'],
        )
        ->create(['dono_id' => $dono->id]);

    Reserva::factory()->count(3)->create(['quadra_id' => $quadras->first()->id]);

    $preview = Livewire::actingAs($dono)
        ->test(Dashboard::class)
        ->instance()
        ->quadras;

    expect($preview)->toHaveCount(5)
        ->and($preview->first()->nome)->toBe('Quadra A')
        ->and($preview->first()->reservas_count)->toBe(3)
        ->and($preview->pluck('nome')->all())->toBe(['Quadra A', 'Quadra B', 'Quadra C', 'Quadra D', 'Quadra E']);
});

test('rota painel.dashboard renderiza o dashboard com link para a listagem completa de quadras', function () {
    $dono = User::factory()->donoQuadra()->create();
    Quadra::factory()->create(['dono_id' => $dono->id, 'nome' => 'Quadra Principal']);

    $this->actingAs($dono)
        ->get(route('painel.dashboard'))
        ->assertOk()
        ->assertSeeLivewire(Dashboard::class)
        ->assertSee('Quadra Principal')
        ->assertSee(route('painel.quadras'));
});
