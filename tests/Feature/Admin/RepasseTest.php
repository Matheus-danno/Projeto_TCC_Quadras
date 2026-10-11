<?php

use App\Enums\ReservaStatus;
use App\Enums\StatusPagamento;
use App\Livewire\Admin\Financeiro\Repasses;
use App\Models\Quadra;
use App\Models\Repasse;
use App\Models\Reserva;
use App\Models\User;
use Livewire\Livewire;

test('valor pendente de repasse é o que o proprietário gerou menos o que já foi repassado', function () {
    $admin = User::factory()->admin()->create();
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id, 'valor_hora' => 100]);

    Reserva::create([
        'quadra_id' => $quadra->id,
        'user_id' => User::factory()->create()->id,
        'data' => now()->addDay()->toDateString(),
        'hora_inicio' => '08:00:00',
        'hora_fim' => '09:00:00',
        'status' => ReservaStatus::Confirmada,
        'status_pagamento' => StatusPagamento::Pago,
    ]);

    Repasse::create([
        'dono_id' => $dono->id,
        'valor_centavos' => 2000,
        'criado_por_id' => $admin->id,
    ]);

    Livewire::actingAs($admin)->test(Repasses::class)
        ->assertSet('donoSelecionadoId', null);

    $linha = (new Repasses)->donos()->firstWhere('dono.id', $dono->id);

    // Quadra gera 95% de R$100 = R$95 (9500 centavos); já repassado R$20 (2000 centavos).
    expect($linha['gerado'])->toBe(95.0)
        ->and($linha['repassado'])->toBe(20.0)
        ->and($linha['pendente'])->toBe(75.0);
});

test('admin consegue marcar um repasse como pago pela tela', function () {
    $admin = User::factory()->admin()->create();
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id, 'valor_hora' => 100]);

    Reserva::create([
        'quadra_id' => $quadra->id,
        'user_id' => User::factory()->create()->id,
        'data' => now()->addDay()->toDateString(),
        'hora_inicio' => '08:00:00',
        'hora_fim' => '09:00:00',
        'status' => ReservaStatus::Confirmada,
        'status_pagamento' => StatusPagamento::Pago,
    ]);

    Livewire::actingAs($admin)->test(Repasses::class)
        ->call('abrirMarcarPago', $dono->id)
        ->set('valor', '95.00')
        ->set('observacao', 'Pix manual')
        ->call('marcarPago');

    $this->assertDatabaseHas('repasses', [
        'dono_id' => $dono->id,
        'valor_centavos' => 9500,
        'criado_por_id' => $admin->id,
        'observacao' => 'Pix manual',
    ]);
});

test('jogador não pode acessar a tela de repasses', function () {
    $jogador = User::factory()->create();

    $this->actingAs($jogador)->get(route('admin.financeiro.repasses'))->assertForbidden();
});
