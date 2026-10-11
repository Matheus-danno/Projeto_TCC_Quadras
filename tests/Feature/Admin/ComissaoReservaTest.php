<?php

use App\Enums\ReservaStatus;
use App\Enums\StatusPagamento;
use App\Models\ComissaoConfiguracao;
use App\Models\Quadra;
use App\Models\Reserva;
use App\Models\User;

function criarReservaDeUmaHora(Quadra $quadra, ReservaStatus $status, StatusPagamento $statusPagamento): Reserva
{
    return Reserva::create([
        'quadra_id' => $quadra->id,
        'user_id' => User::factory()->create()->id,
        'data' => now()->addDay()->toDateString(),
        'hora_inicio' => '08:00:00',
        'hora_fim' => '09:00:00',
        'status' => $status,
        'status_pagamento' => $statusPagamento,
    ]);
}

test('reserva confirmada e paga recebe o snapshot de comissão com o percentual padrão', function () {
    $quadra = Quadra::factory()->create(['valor_hora' => 100]);

    $reserva = criarReservaDeUmaHora($quadra, ReservaStatus::Confirmada, StatusPagamento::Pago);

    expect((float) $reserva->comissao_percentual)->toBe(5.0)
        ->and($reserva->valor_total_centavos)->toBe(10000)
        ->and($reserva->comissao_valor_centavos)->toBe(500)
        ->and($reserva->valor_proprietario_centavos)->toBe(9500);
});

test('reserva pendente não gera comissão', function () {
    $quadra = Quadra::factory()->create(['valor_hora' => 100]);

    $reserva = criarReservaDeUmaHora($quadra, ReservaStatus::Pendente, StatusPagamento::Pendente);

    expect($reserva->comissao_valor_centavos)->toBeNull();
});

test('reserva confirmada mas isenta não gera comissão', function () {
    $quadra = Quadra::factory()->create(['valor_hora' => 100]);

    $reserva = criarReservaDeUmaHora($quadra, ReservaStatus::Confirmada, StatusPagamento::Isento);

    expect($reserva->comissao_valor_centavos)->toBeNull();
});

test('cancelar uma reserva já paga mantém o snapshot de comissão congelado (para o estorno), não gera comissão nova', function () {
    $quadra = Quadra::factory()->create(['valor_hora' => 100]);

    $reserva = criarReservaDeUmaHora($quadra, ReservaStatus::Confirmada, StatusPagamento::Pago);

    expect($reserva->comissao_valor_centavos)->toBe(500);

    $reserva->update(['status' => ReservaStatus::Cancelada, 'cancelamento_tipo' => 'credito']);

    expect($reserva->comissao_valor_centavos)->toBe(500)
        ->and($reserva->status)->toBe(ReservaStatus::Cancelada);
});

test('mudar o percentual de comissão não afeta reservas já computadas, só as futuras', function () {
    $quadra = Quadra::factory()->create(['valor_hora' => 100]);

    ComissaoConfiguracao::definirPercentual(5.0, null);
    $reservaAntiga = criarReservaDeUmaHora($quadra, ReservaStatus::Confirmada, StatusPagamento::Pago);

    ComissaoConfiguracao::definirPercentual(10.0, null);
    $reservaNova = criarReservaDeUmaHora($quadra, ReservaStatus::Confirmada, StatusPagamento::Pago);

    expect((float) $reservaAntiga->refresh()->comissao_percentual)->toBe(5.0)
        ->and($reservaAntiga->comissao_valor_centavos)->toBe(500)
        ->and((float) $reservaNova->comissao_percentual)->toBe(10.0)
        ->and($reservaNova->comissao_valor_centavos)->toBe(1000);
});

test('percentual atual cai no valor padrão de config quando não há nenhuma configuração salva', function () {
    expect(ComissaoConfiguracao::percentualAtual())->toBe((float) config('comissao.percentual_padrao'));
});
