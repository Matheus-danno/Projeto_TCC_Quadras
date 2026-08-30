<?php

use App\Enums\ReservaStatus;
use App\Livewire\Painel\Financeiro;
use App\Models\Quadra;
use App\Models\Reserva;
use App\Models\User;
use Livewire\Livewire;

test('rota painel.financeiro renderiza o componente', function () {
    $dono = User::factory()->donoQuadra()->create();

    $this->actingAs($dono)
        ->get(route('painel.financeiro'))
        ->assertOk()
        ->assertSeeLivewire(Financeiro::class);
});

test('faturamento do mês atual soma apenas reservas confirmadas do dono no período', function () {
    $dono = User::factory()->donoQuadra()->create();
    $outroDono = User::factory()->donoQuadra()->create();

    $quadra = Quadra::factory()->create(['dono_id' => $dono->id, 'valor_hora' => 100]);
    $quadraOutroDono = Quadra::factory()->create(['dono_id' => $outroDono->id, 'valor_hora' => 500]);

    // Confirmada, 2h, dentro do mês atual: conta (100 * 2 = 200).
    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => now()->startOfMonth()->addDays(2)->toDateString(),
        'hora_inicio' => '08:00:00',
        'hora_fim' => '10:00:00',
        'status' => ReservaStatus::Confirmada,
    ]);

    // Pendente, dentro do mês atual: não conta.
    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => now()->startOfMonth()->addDays(3)->toDateString(),
        'hora_inicio' => '08:00:00',
        'hora_fim' => '09:00:00',
        'status' => ReservaStatus::Pendente,
    ]);

    // Confirmada, mas no mês passado: não conta no período padrão.
    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => now()->subMonthNoOverflow()->startOfMonth()->addDays(2)->toDateString(),
        'hora_inicio' => '08:00:00',
        'hora_fim' => '10:00:00',
        'status' => ReservaStatus::Confirmada,
    ]);

    // Confirmada, dentro do mês, mas de outro dono: não conta.
    Reserva::factory()->create([
        'quadra_id' => $quadraOutroDono->id,
        'data' => now()->startOfMonth()->addDays(2)->toDateString(),
        'status' => ReservaStatus::Confirmada,
    ]);

    $component = Livewire::actingAs($dono)->test(Financeiro::class);

    expect((float) $component->instance()->faturamento)->toBe(200.0)
        ->and($component->instance()->reservas->total())->toBe(1);
});

test('filtro mês passado calcula o faturamento do mês anterior', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id, 'valor_hora' => 50]);

    // Confirmada de 3h no mês passado: conta (50 * 3 = 150).
    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => now()->subMonthNoOverflow()->startOfMonth()->addDays(1)->toDateString(),
        'hora_inicio' => '08:00:00',
        'hora_fim' => '11:00:00',
        'status' => ReservaStatus::Confirmada,
    ]);

    // Confirmada no mês atual: não deve contar quando o filtro é "mês passado".
    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => now()->startOfMonth()->addDays(1)->toDateString(),
        'status' => ReservaStatus::Confirmada,
    ]);

    $faturamento = Livewire::actingAs($dono)
        ->test(Financeiro::class)
        ->set('periodo', 'mes_passado')
        ->instance()
        ->faturamento;

    expect((float) $faturamento)->toBe(150.0);
});

test('período personalizado filtra o faturamento pelo intervalo de datas informado', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id, 'valor_hora' => 80]);

    $dentroDoIntervalo = now()->subDays(10);
    $foraDoIntervalo = now()->subDays(40);

    // Confirmada de 1h dentro do intervalo customizado: conta (80 * 1 = 80).
    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => $dentroDoIntervalo->toDateString(),
        'hora_inicio' => '08:00:00',
        'hora_fim' => '09:00:00',
        'status' => ReservaStatus::Confirmada,
    ]);

    // Confirmada fora do intervalo customizado: não conta.
    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => $foraDoIntervalo->toDateString(),
        'status' => ReservaStatus::Confirmada,
    ]);

    $faturamento = Livewire::actingAs($dono)
        ->test(Financeiro::class)
        ->set('periodo', 'personalizado')
        ->set('dataInicio', now()->subDays(15)->toDateString())
        ->set('dataFim', now()->subDays(5)->toDateString())
        ->instance()
        ->faturamento;

    expect((float) $faturamento)->toBe(80.0);
});

test('faturamento por quadra agrupa e soma corretamente cada quadra do dono', function () {
    $dono = User::factory()->donoQuadra()->create();

    $quadraA = Quadra::factory()->create(['dono_id' => $dono->id, 'nome' => 'Quadra A', 'valor_hora' => 100]);
    $quadraB = Quadra::factory()->create(['dono_id' => $dono->id, 'nome' => 'Quadra B', 'valor_hora' => 60]);

    // Quadra A: duas reservas de 1h confirmadas = 200.
    Reserva::factory()->count(2)->create([
        'quadra_id' => $quadraA->id,
        'data' => now()->startOfMonth()->addDays(2)->toDateString(),
        'hora_inicio' => '08:00:00',
        'hora_fim' => '09:00:00',
        'status' => ReservaStatus::Confirmada,
    ]);

    // Quadra B: uma reserva de 1h confirmada = 60.
    Reserva::factory()->create([
        'quadra_id' => $quadraB->id,
        'data' => now()->startOfMonth()->addDays(2)->toDateString(),
        'hora_inicio' => '08:00:00',
        'hora_fim' => '09:00:00',
        'status' => ReservaStatus::Confirmada,
    ]);

    $porQuadra = Livewire::actingAs($dono)
        ->test(Financeiro::class)
        ->instance()
        ->faturamentoPorQuadra;

    expect($porQuadra)->toHaveCount(2);

    $linhaA = $porQuadra->firstWhere('quadra.id', $quadraA->id);
    $linhaB = $porQuadra->firstWhere('quadra.id', $quadraB->id);

    expect($linhaA['reservas'])->toBe(2)
        ->and((float) $linhaA['faturamento'])->toBe(200.0)
        ->and($linhaB['reservas'])->toBe(1)
        ->and((float) $linhaB['faturamento'])->toBe(60.0);

    // Ordenado do maior para o menor faturamento.
    expect($porQuadra->first()['quadra']->id)->toBe($quadraA->id);
});

test('tabela de reservas do período traz apenas confirmadas e é paginada', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id]);

    Reserva::factory()->count(12)->create([
        'quadra_id' => $quadra->id,
        'data' => now()->startOfMonth()->addDays(2)->toDateString(),
        'status' => ReservaStatus::Confirmada,
    ]);

    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => now()->startOfMonth()->addDays(2)->toDateString(),
        'status' => ReservaStatus::Pendente,
    ]);

    $reservas = Livewire::actingAs($dono)
        ->test(Financeiro::class)
        ->instance()
        ->reservas;

    expect($reservas->total())->toBe(12)
        ->and($reservas->count())->toBe(10);
});
