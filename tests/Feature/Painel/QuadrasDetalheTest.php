<?php

use App\Enums\ReservaStatus;
use App\Livewire\Painel\Quadras\Detalhe;
use App\Models\AvaliacaoQuadra;
use App\Models\Quadra;
use App\Models\Reserva;
use App\Models\User;
use Livewire\Livewire;

test('dono vê os detalhes e as próximas reservas da própria quadra', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id, 'nome' => 'Arena Central']);
    $cliente = User::factory()->create(['name' => 'Cliente Teste']);

    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'user_id' => $cliente->id,
        'data' => now()->addDay()->toDateString(),
        'status' => ReservaStatus::Confirmada,
    ]);

    $this->actingAs($dono)
        ->get(route('painel.quadras.show', $quadra))
        ->assertOk()
        ->assertSeeLivewire(Detalhe::class)
        ->assertSee('Arena Central')
        ->assertSee('Cliente Teste');
});

test('dono não acessa detalhes de quadra de outro dono', function () {
    $dono = User::factory()->donoQuadra()->create();
    $outroDono = User::factory()->donoQuadra()->create();
    $quadraAlheia = Quadra::factory()->create(['dono_id' => $outroDono->id]);

    $this->actingAs($dono)
        ->get(route('painel.quadras.show', $quadraAlheia))
        ->assertForbidden();
});

test('proximasReservas ficam limitadas às 10 mais próximas da quadra', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id]);

    Reserva::factory()->count(12)->create([
        'quadra_id' => $quadra->id,
        'data' => now()->addDay()->toDateString(),
        'status' => ReservaStatus::Confirmada,
    ]);

    $reservas = Livewire::actingAs($dono)
        ->test(Detalhe::class, ['quadra' => $quadra])
        ->instance()
        ->proximasReservas;

    expect($reservas)->toHaveCount(10);
});

test('proximasReservas exclui reservas passadas e canceladas', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id]);

    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => now()->addDay()->toDateString(),
        'status' => ReservaStatus::Confirmada,
        'user_id' => User::factory()->create(['name' => 'Reserva Futura'])->id,
    ]);

    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => now()->subDay()->toDateString(),
        'status' => ReservaStatus::Confirmada,
        'user_id' => User::factory()->create(['name' => 'Reserva Passada'])->id,
    ]);

    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => now()->addDay()->toDateString(),
        'status' => ReservaStatus::Cancelada,
        'user_id' => User::factory()->create(['name' => 'Reserva Cancelada'])->id,
    ]);

    $reservas = Livewire::actingAs($dono)
        ->test(Detalhe::class, ['quadra' => $quadra])
        ->instance()
        ->proximasReservas;

    expect($reservas)->toHaveCount(1)
        ->and($reservas->first()->nome_cliente)->toBe('Reserva Futura');
});

test('dono desativa e reativa a quadra pela tela de detalhes', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id, 'ativa' => true]);

    $component = Livewire::actingAs($dono)
        ->test(Detalhe::class, ['quadra' => $quadra])
        ->call('cancelar');

    expect($quadra->fresh()->ativa)->toBeFalse();

    $component->call('ativar');

    expect($quadra->fresh()->ativa)->toBeTrue();
});

test('indicadores calculam total de reservas, faturamento e avaliação média com padrão 5', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id, 'valor_hora' => 100]);

    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'status' => ReservaStatus::Confirmada,
        'hora_inicio' => '10:00:00',
        'hora_fim' => '12:00:00',
    ]);

    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'status' => ReservaStatus::Cancelada,
    ]);

    $indicadores = Livewire::actingAs($dono)
        ->test(Detalhe::class, ['quadra' => $quadra])
        ->instance()
        ->indicadores;

    expect($indicadores['totalReservas'])->toBe(2)
        ->and($indicadores['faturamentoGerado'])->toEqual(200.0)
        ->and($indicadores['avaliacaoMedia'])->toBe(5.0);
});

test('indicadores usam a média real quando a quadra tem avaliações', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id]);

    AvaliacaoQuadra::factory()->create(['quadra_id' => $quadra->id, 'nota' => 4]);
    AvaliacaoQuadra::factory()->create(['quadra_id' => $quadra->id, 'nota' => 2]);

    $indicadores = Livewire::actingAs($dono)
        ->test(Detalhe::class, ['quadra' => $quadra])
        ->instance()
        ->indicadores;

    expect($indicadores['avaliacaoMedia'])->toBe(3.0);
});

test('avaliacoesRecentes traz as avaliações mais recentes com o autor carregado', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id]);
    $autor = User::factory()->create(['name' => 'Autor da Avaliação']);

    AvaliacaoQuadra::factory()->create(['quadra_id' => $quadra->id, 'autor_id' => $autor->id, 'comentario' => 'Muito boa!']);

    $avaliacoes = Livewire::actingAs($dono)
        ->test(Detalhe::class, ['quadra' => $quadra])
        ->instance()
        ->avaliacoesRecentes;

    expect($avaliacoes)->toHaveCount(1)
        ->and($avaliacoes->first()->autor->name)->toBe('Autor da Avaliação');
});
