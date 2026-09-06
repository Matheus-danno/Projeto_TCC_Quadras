<?php

use App\Enums\ReservaStatus;
use App\Enums\StatusPagamento;
use App\Livewire\Painel\AgendamentoManual\Criar;
use App\Models\Quadra;
use App\Models\Reserva;
use App\Models\User;
use Livewire\Livewire;

test('rota painel.agendamento-manual renderiza o componente', function () {
    $dono = User::factory()->donoQuadra()->create();

    $this->actingAs($dono)
        ->get(route('painel.agendamento-manual'))
        ->assertOk()
        ->assertSeeLivewire(Criar::class);
});

test('dono agenda uma reserva confirmada para um cliente com conta', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id, 'ativa' => true, 'valor_hora' => 50]);
    $cliente = User::factory()->create(['name' => 'João Cliente', 'email' => 'joao.cliente@example.com']);

    Livewire::actingAs($dono)
        ->test(Criar::class)
        ->set('quadraId', (string) $quadra->id)
        ->set('data', now()->addDay()->toDateString())
        ->set('horaInicio', '10:00')
        ->set('horaFim', '11:00')
        ->set('tipoCliente', 'existente')
        ->set('buscaCliente', 'joao.cliente')
        ->call('selecionarCliente', $cliente->id)
        ->set('statusPagamento', 'pago')
        ->set('formaPagamento', 'pix')
        ->set('observacoes', 'Cliente prefere a quadra coberta.')
        ->call('salvar')
        ->assertHasNoErrors();

    expect(Reserva::count())->toBe(1);

    $reserva = Reserva::first();

    expect($reserva->quadra_id)->toBe($quadra->id)
        ->and($reserva->user_id)->toBe($cliente->id)
        ->and($reserva->cliente_nome)->toBeNull()
        ->and($reserva->status)->toBe(ReservaStatus::Confirmada)
        ->and(substr($reserva->hora_inicio, 0, 5))->toBe('10:00')
        ->and(substr($reserva->hora_fim, 0, 5))->toBe('11:00')
        ->and((float) $reserva->valor)->toBe(50.0)
        ->and($reserva->status_pagamento)->toBe(StatusPagamento::Pago)
        ->and($reserva->metodo_pagamento)->toBe('pix')
        ->and($reserva->observacoes)->toBe('Cliente prefere a quadra coberta.');
});

test('dono agenda uma reserva confirmada para um cliente sem conta no sistema', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id, 'ativa' => true]);

    Livewire::actingAs($dono)
        ->test(Criar::class)
        ->set('quadraId', (string) $quadra->id)
        ->set('data', now()->addDay()->toDateString())
        ->set('horaInicio', '14:00')
        ->set('horaFim', '15:00')
        ->set('tipoCliente', 'sem_conta')
        ->set('clienteNome', 'Maria Sem Conta')
        ->set('clienteTelefone', '11912345678')
        ->set('clienteEmail', 'maria@example.com')
        ->set('formaPagamento', 'dinheiro')
        ->call('salvar')
        ->assertHasNoErrors();

    expect(Reserva::count())->toBe(1);

    $reserva = Reserva::first();

    expect($reserva->user_id)->toBeNull()
        ->and($reserva->cliente_nome)->toBe('Maria Sem Conta')
        ->and($reserva->cliente_telefone)->toBe('11912345678')
        ->and($reserva->cliente_email)->toBe('maria@example.com')
        ->and($reserva->status)->toBe(ReservaStatus::Confirmada)
        ->and($reserva->status_pagamento)->toBe(StatusPagamento::Pendente)
        ->and($reserva->metodo_pagamento)->toBe('dinheiro');
});

test('agendamento isento de pagamento não exige forma de pagamento', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id, 'ativa' => true]);

    Livewire::actingAs($dono)
        ->test(Criar::class)
        ->set('quadraId', (string) $quadra->id)
        ->set('data', now()->addDay()->toDateString())
        ->set('horaInicio', '16:00')
        ->set('horaFim', '17:00')
        ->set('tipoCliente', 'sem_conta')
        ->set('clienteNome', 'Amigo do Dono')
        ->set('clienteTelefone', '11900000000')
        ->set('statusPagamento', 'isento')
        ->call('salvar')
        ->assertHasNoErrors();

    $reserva = Reserva::first();

    expect($reserva->status_pagamento)->toBe(StatusPagamento::Isento)
        ->and($reserva->metodo_pagamento)->toBeNull();
});

test('agendamento é rejeitado quando o horário conflita com uma reserva existente', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id, 'ativa' => true]);
    $data = now()->addDay()->toDateString();

    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => $data,
        'hora_inicio' => '10:00:00',
        'hora_fim' => '11:00:00',
        'status' => ReservaStatus::Confirmada,
    ]);

    Livewire::actingAs($dono)
        ->test(Criar::class)
        ->set('quadraId', (string) $quadra->id)
        ->set('data', $data)
        ->set('horaInicio', '10:30')
        ->set('horaFim', '11:30')
        ->set('tipoCliente', 'sem_conta')
        ->set('clienteNome', 'Cliente Conflitante')
        ->set('clienteTelefone', '11900000000')
        ->set('formaPagamento', 'pix')
        ->call('salvar')
        ->assertHasErrors('horaInicio');

    expect(Reserva::count())->toBe(1);
});

test('agendamento não conflita com reserva cancelada no mesmo horário', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id, 'ativa' => true]);
    $data = now()->addDay()->toDateString();

    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => $data,
        'hora_inicio' => '10:00:00',
        'hora_fim' => '11:00:00',
        'status' => ReservaStatus::Cancelada,
    ]);

    Livewire::actingAs($dono)
        ->test(Criar::class)
        ->set('quadraId', (string) $quadra->id)
        ->set('data', $data)
        ->set('horaInicio', '10:00')
        ->set('horaFim', '11:00')
        ->set('tipoCliente', 'sem_conta')
        ->set('clienteNome', 'Cliente Livre')
        ->set('clienteTelefone', '11900000000')
        ->set('formaPagamento', 'pix')
        ->call('salvar')
        ->assertHasNoErrors();

    expect(Reserva::count())->toBe(2);
});

test('dono não consegue agendar em quadra de outro dono', function () {
    $dono = User::factory()->donoQuadra()->create();
    $outroDono = User::factory()->donoQuadra()->create();
    $quadraAlheia = Quadra::factory()->create(['dono_id' => $outroDono->id, 'ativa' => true]);

    Livewire::actingAs($dono)
        ->test(Criar::class)
        ->set('quadraId', (string) $quadraAlheia->id)
        ->set('data', now()->addDay()->toDateString())
        ->set('horaInicio', '10:00')
        ->set('horaFim', '11:00')
        ->set('tipoCliente', 'sem_conta')
        ->set('clienteNome', 'Cliente Qualquer')
        ->set('clienteTelefone', '11900000000')
        ->set('formaPagamento', 'pix')
        ->call('salvar')
        ->assertForbidden();

    expect(Reserva::count())->toBe(0);
});

test('busca de clientes encontra apenas jogadores por nome ou e-mail', function () {
    $dono = User::factory()->donoQuadra()->create();
    $jogador = User::factory()->create(['name' => 'Carlos Jogador', 'email' => 'carlos@example.com']);
    User::factory()->donoQuadra()->create(['name' => 'Carlos Dono', 'email' => 'carlos.dono@example.com']);

    $encontrados = Livewire::actingAs($dono)
        ->test(Criar::class)
        ->set('buscaCliente', 'Carlos')
        ->instance()
        ->clientesEncontrados;

    expect($encontrados)->toHaveCount(1)
        ->and($encontrados->first()->id)->toBe($jogador->id);
});
