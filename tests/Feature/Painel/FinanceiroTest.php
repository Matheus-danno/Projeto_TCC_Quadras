<?php

use App\Enums\PedidoStatus;
use App\Enums\ReservaStatus;
use App\Enums\StatusPagamento;
use App\Livewire\Painel\Financeiro;
use App\Models\ItemPedido;
use App\Models\Pedido;
use App\Models\Produto;
use App\Models\Quadra;
use App\Models\Reserva;
use App\Models\User;
use Livewire\Livewire;

function criarPedidoParaFinanceiro(
    User $dono,
    User $comprador,
    Produto $produto,
    int $quantidade = 1,
    PedidoStatus $status = PedidoStatus::Aguardando
): Pedido {
    $total = $produto->preco * $quantidade;

    $pedido = Pedido::create([
        'user_id' => $comprador->id,
        'dono_id' => $dono->id,
        'status' => $status,
        'total' => $total,
        'numero_retirada' => Pedido::gerarNumeroRetirada(),
        'comissao_percentual' => 5,
        'comissao_valor' => round($total * 0.05, 2),
    ]);

    ItemPedido::create([
        'pedido_id' => $pedido->id,
        'produto_id' => $produto->id,
        'quantidade' => $quantidade,
        'preco_unitario' => $produto->preco,
    ]);

    return $pedido;
}

test('rota painel.financeiro renderiza o componente', function () {
    $dono = User::factory()->donoQuadra()->create();

    $this->actingAs($dono)
        ->get(route('painel.financeiro'))
        ->assertOk()
        ->assertSeeLivewire(Financeiro::class);
});

test('faturamento por quadra soma apenas reservas confirmadas e pagas do mês atual, do dono', function () {
    $dono = User::factory()->donoQuadra()->create();
    $outroDono = User::factory()->donoQuadra()->create();

    $quadra = Quadra::factory()->create(['dono_id' => $dono->id, 'valor_hora' => 100]);
    $quadraOutroDono = Quadra::factory()->create(['dono_id' => $outroDono->id, 'valor_hora' => 500]);

    // Confirmada, paga, 2h, dentro do mês atual: conta (100 * 2 = 200).
    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => now()->startOfMonth()->addDays(2)->toDateString(),
        'hora_inicio' => '08:00:00',
        'hora_fim' => '10:00:00',
        'status' => ReservaStatus::Confirmada,
        'status_pagamento' => StatusPagamento::Pago,
    ]);

    // Confirmada, mas isenta: não conta.
    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => now()->startOfMonth()->addDays(2)->toDateString(),
        'hora_inicio' => '11:00:00',
        'hora_fim' => '12:00:00',
        'status' => ReservaStatus::Confirmada,
        'status_pagamento' => StatusPagamento::Isento,
    ]);

    // Pendente, dentro do mês atual: não conta.
    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => now()->startOfMonth()->addDays(3)->toDateString(),
        'hora_inicio' => '08:00:00',
        'hora_fim' => '09:00:00',
        'status' => ReservaStatus::Pendente,
    ]);

    // Confirmada, mas no mês passado: não conta.
    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => now()->subMonthNoOverflow()->startOfMonth()->addDays(2)->toDateString(),
        'hora_inicio' => '08:00:00',
        'hora_fim' => '10:00:00',
        'status' => ReservaStatus::Confirmada,
        'status_pagamento' => StatusPagamento::Pago,
    ]);

    // Confirmada, dentro do mês, mas de outro dono: não conta.
    Reserva::factory()->create([
        'quadra_id' => $quadraOutroDono->id,
        'data' => now()->startOfMonth()->addDays(2)->toDateString(),
        'status' => ReservaStatus::Confirmada,
        'status_pagamento' => StatusPagamento::Pago,
    ]);

    $component = Livewire::actingAs($dono)->test(Financeiro::class);

    expect((float) $component->instance()->faturamentoTotalMes)->toBe(200.0);
});

test('faturamento por quadra usa o valor lançado manualmente quando existir', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id, 'valor_hora' => 100]);

    // Valor manual de 70, mesmo a quadra custando 100/hora.
    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => now()->startOfMonth()->addDays(2)->toDateString(),
        'hora_inicio' => '08:00:00',
        'hora_fim' => '09:00:00',
        'status' => ReservaStatus::Confirmada,
        'status_pagamento' => StatusPagamento::Pago,
        'valor' => 70,
    ]);

    $faturamento = Livewire::actingAs($dono)
        ->test(Financeiro::class)
        ->instance()
        ->faturamentoTotalMes;

    expect((float) $faturamento)->toBe(70.0);
});

test('faturamento por quadra agrupa, soma e ordena do maior para o menor, com percentual relativo', function () {
    $dono = User::factory()->donoQuadra()->create();

    $quadraA = Quadra::factory()->create(['dono_id' => $dono->id, 'nome' => 'Quadra A', 'valor_hora' => 100]);
    $quadraB = Quadra::factory()->create(['dono_id' => $dono->id, 'nome' => 'Quadra B', 'valor_hora' => 60]);

    // Quadra A: duas reservas de 1h confirmadas e pagas = 200.
    Reserva::factory()->count(2)->create([
        'quadra_id' => $quadraA->id,
        'data' => now()->startOfMonth()->addDays(2)->toDateString(),
        'hora_inicio' => '08:00:00',
        'hora_fim' => '09:00:00',
        'status' => ReservaStatus::Confirmada,
        'status_pagamento' => StatusPagamento::Pago,
    ]);

    // Quadra B: uma reserva de 1h confirmada e paga = 60.
    Reserva::factory()->create([
        'quadra_id' => $quadraB->id,
        'data' => now()->startOfMonth()->addDays(2)->toDateString(),
        'hora_inicio' => '08:00:00',
        'hora_fim' => '09:00:00',
        'status' => ReservaStatus::Confirmada,
        'status_pagamento' => StatusPagamento::Pago,
    ]);

    $porQuadra = Livewire::actingAs($dono)
        ->test(Financeiro::class)
        ->instance()
        ->faturamentoPorQuadra;

    expect($porQuadra)->toHaveCount(2);

    $linhaA = $porQuadra->firstWhere('quadra.id', $quadraA->id);
    $linhaB = $porQuadra->firstWhere('quadra.id', $quadraB->id);

    expect((float) $linhaA['faturamento'])->toBe(200.0)
        ->and($linhaA['percentual'])->toBe(100.0)
        ->and((float) $linhaB['faturamento'])->toBe(60.0)
        ->and($linhaB['percentual'])->toBe(30.0);

    expect($porQuadra->first()['quadra']->id)->toBe($quadraA->id);
});

test('proxima liberacao aponta a reserva confirmada e paga mais próxima no futuro', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id, 'valor_hora' => 80]);

    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => now()->addDays(10)->toDateString(),
        'hora_inicio' => '08:00:00',
        'hora_fim' => '09:00:00',
        'status' => ReservaStatus::Confirmada,
        'status_pagamento' => StatusPagamento::Pago,
    ]);

    // Mais próxima: em 3 dias.
    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => now()->addDays(3)->toDateString(),
        'hora_inicio' => '08:00:00',
        'hora_fim' => '09:00:00',
        'status' => ReservaStatus::Confirmada,
        'status_pagamento' => StatusPagamento::Pago,
    ]);

    // Isenta e mais próxima ainda, mas não deve contar.
    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => now()->addDay()->toDateString(),
        'hora_inicio' => '08:00:00',
        'hora_fim' => '09:00:00',
        'status' => ReservaStatus::Confirmada,
        'status_pagamento' => StatusPagamento::Isento,
    ]);

    $proximaLiberacao = Livewire::actingAs($dono)
        ->test(Financeiro::class)
        ->instance()
        ->proximaLiberacao;

    expect($proximaLiberacao['dias'])->toBe(3)
        ->and((float) $proximaLiberacao['valor'])->toBe(80.0);
});

test('transações recentes trazem confirmadas e canceladas com reembolso, com status correto', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id]);
    $cliente = User::factory()->create();

    $paga = Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'user_id' => $cliente->id,
        'data' => now()->startOfMonth()->addDays(2)->toDateString(),
        'status' => ReservaStatus::Confirmada,
        'status_pagamento' => StatusPagamento::Pago,
    ]);

    $reembolsada = Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'user_id' => $cliente->id,
        'data' => now()->startOfMonth()->addDays(3)->toDateString(),
        'status' => ReservaStatus::Cancelada,
        'cancelamento_tipo' => 'credito',
    ]);

    // Pendente (nunca confirmada): não é uma transação.
    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => now()->startOfMonth()->addDays(4)->toDateString(),
        'status' => ReservaStatus::Pendente,
    ]);

    // Cancelada sem reembolso (nunca foi paga): não é uma transação.
    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => now()->startOfMonth()->addDays(5)->toDateString(),
        'status' => ReservaStatus::Cancelada,
        'cancelamento_tipo' => null,
    ]);

    $component = Livewire::actingAs($dono)->test(Financeiro::class);

    $transacoes = $component->instance()->transacoes;

    expect($transacoes->total())->toBe(2);

    $statusPaga = $component->instance()->statusTransacao($paga->fresh());
    $statusReembolsada = $component->instance()->statusTransacao($reembolsada->fresh());

    expect($statusPaga['label'])->toBe('Pago')
        ->and($statusReembolsada['label'])->toBe('Reembolsado');
});

test('busca filtra transações por nome do cliente ou nome da quadra', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id, 'nome' => 'Quadra Central']);

    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'user_id' => null,
        'cliente_nome' => 'Fulano de Tal',
        'data' => now()->startOfMonth()->addDays(2)->toDateString(),
        'status' => ReservaStatus::Confirmada,
        'status_pagamento' => StatusPagamento::Pago,
    ]);

    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'user_id' => null,
        'cliente_nome' => 'Beltrano da Silva',
        'data' => now()->startOfMonth()->addDays(3)->toDateString(),
        'status' => ReservaStatus::Confirmada,
        'status_pagamento' => StatusPagamento::Pago,
    ]);

    $transacoes = Livewire::actingAs($dono)
        ->test(Financeiro::class)
        ->set('busca', 'Fulano')
        ->instance()
        ->transacoes;

    expect($transacoes->total())->toBe(1)
        ->and($transacoes->first()['cliente'])->toBe('Fulano de Tal');
});

test('filtro de status isola apenas transações reembolsadas', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id]);
    $cliente = User::factory()->create();

    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'user_id' => $cliente->id,
        'data' => now()->startOfMonth()->addDays(2)->toDateString(),
        'status' => ReservaStatus::Confirmada,
        'status_pagamento' => StatusPagamento::Pago,
    ]);

    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'user_id' => $cliente->id,
        'data' => now()->startOfMonth()->addDays(3)->toDateString(),
        'status' => ReservaStatus::Cancelada,
        'cancelamento_tipo' => 'credito',
    ]);

    $transacoes = Livewire::actingAs($dono)
        ->test(Financeiro::class)
        ->set('filtroStatus', 'reembolsado')
        ->instance()
        ->transacoes;

    expect($transacoes->total())->toBe(1)
        ->and($transacoes->first()['statusLabel'])->toBe('Reembolsado');
});

test('dono cadastra e visualiza a chave Pix de recebimento mascarada', function () {
    $dono = User::factory()->donoQuadra()->create();

    Livewire::actingAs($dono)
        ->test(Financeiro::class)
        ->call('editarChavePix')
        ->set('chavePixRecebimento', 'ana.silva@email.com')
        ->call('salvarChavePix')
        ->assertHasNoErrors();

    expect($dono->fresh()->chave_pix_recebimento)->toBe('ana.silva@email.com');

    $mascarada = Livewire::actingAs($dono)
        ->test(Financeiro::class)
        ->instance()
        ->chavePixMascarada();

    expect($mascarada)->toBe('••••.silva@email.com');
});

test('faturamento dos últimos 6 meses traz o mês atual por último e ignora reservas fora do intervalo', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id, 'valor_hora' => 100]);

    // Mês atual: 1h confirmada e paga = 100.
    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => now()->startOfMonth()->addDays(2)->toDateString(),
        'hora_inicio' => '08:00:00',
        'hora_fim' => '09:00:00',
        'status' => ReservaStatus::Confirmada,
        'status_pagamento' => StatusPagamento::Pago,
    ]);

    // 2 meses atrás: 1h confirmada e paga = 100.
    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => now()->subMonthsNoOverflow(2)->startOfMonth()->addDays(2)->toDateString(),
        'hora_inicio' => '08:00:00',
        'hora_fim' => '09:00:00',
        'status' => ReservaStatus::Confirmada,
        'status_pagamento' => StatusPagamento::Pago,
    ]);

    // Fora do intervalo de 6 meses: não deve contar em nenhum mês.
    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => now()->subMonthsNoOverflow(8)->startOfMonth()->addDays(2)->toDateString(),
        'status' => ReservaStatus::Confirmada,
        'status_pagamento' => StatusPagamento::Pago,
    ]);

    $meses = Livewire::actingAs($dono)
        ->test(Financeiro::class)
        ->instance()
        ->faturamentoUltimosMeses;

    expect($meses)->toHaveCount(6)
        ->and($meses[5]['atual'])->toBeTrue()
        ->and((float) $meses[5]['faturamento'])->toBe(100.0)
        ->and($meses[5]['mes']->isSameMonth(now()))->toBeTrue()
        ->and((float) $meses[3]['faturamento'])->toBe(100.0)
        ->and(collect($meses)->sum('faturamento'))->toBe(200.0);
});

test('exportarCsv baixa as transações do mês atual respeitando os filtros ativos', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id, 'nome' => 'Quadra Exportação']);

    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'cliente_nome' => 'Cliente Exportado',
        'data' => now()->startOfMonth()->addDays(2)->toDateString(),
        'status' => ReservaStatus::Confirmada,
        'status_pagamento' => StatusPagamento::Pago,
    ]);

    Livewire::actingAs($dono)
        ->test(Financeiro::class)
        ->call('exportarCsv')
        ->assertFileDownloaded('transacoes-'.now()->format('Y-m').'.csv');
});

test('comissaoReserva calcula 5% do valor da reserva', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id, 'valor_hora' => 100]);

    $reserva = Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'hora_inicio' => '08:00:00',
        'hora_fim' => '10:00:00',
    ]);

    $comissao = Livewire::actingAs($dono)
        ->test(Financeiro::class)
        ->instance()
        ->comissaoReserva($reserva);

    expect($comissao)->toBe(10.0);
});

test('faturamento e comissao da loja somam apenas pedidos nao cancelados do dono no mes atual', function () {
    $dono = User::factory()->donoQuadra()->create();
    $outroDono = User::factory()->donoQuadra()->create();
    $comprador = User::factory()->create();

    $produto = Produto::factory()->create(['dono_id' => $dono->id, 'preco' => 100]);
    $produtoOutroDono = Produto::factory()->create(['dono_id' => $outroDono->id, 'preco' => 500]);

    criarPedidoParaFinanceiro($dono, $comprador, $produto, status: PedidoStatus::Aguardando);
    criarPedidoParaFinanceiro($dono, $comprador, $produto, status: PedidoStatus::Cancelado);
    criarPedidoParaFinanceiro($outroDono, $comprador, $produtoOutroDono, status: PedidoStatus::Aguardando);

    $component = Livewire::actingAs($dono)->test(Financeiro::class);

    expect((float) $component->instance()->faturamentoLojaMes)->toBe(100.0)
        ->and((float) $component->instance()->comissaoLojaMes)->toBe(5.0);
});

test('faturamento por produto agrupa e ordena do maior para o menor, com percentual relativo', function () {
    $dono = User::factory()->donoQuadra()->create();
    $comprador = User::factory()->create();

    $produtoA = Produto::factory()->create(['dono_id' => $dono->id, 'nome' => 'Produto A', 'preco' => 100]);
    $produtoB = Produto::factory()->create(['dono_id' => $dono->id, 'nome' => 'Produto B', 'preco' => 30]);

    criarPedidoParaFinanceiro($dono, $comprador, $produtoA, quantidade: 2);
    criarPedidoParaFinanceiro($dono, $comprador, $produtoB, quantidade: 1);

    $porProduto = Livewire::actingAs($dono)
        ->test(Financeiro::class)
        ->instance()
        ->faturamentoPorProduto;

    expect($porProduto)->toHaveCount(2);

    $linhaA = $porProduto->firstWhere('produto.id', $produtoA->id);
    $linhaB = $porProduto->firstWhere('produto.id', $produtoB->id);

    expect((float) $linhaA['faturamento'])->toBe(200.0)
        ->and($linhaA['percentual'])->toBe(100.0)
        ->and((float) $linhaB['faturamento'])->toBe(30.0)
        ->and($linhaB['percentual'])->toBe(15.0);

    expect($porProduto->first()['produto']->id)->toBe($produtoA->id);
});

test('resumoGeral soma faturamento e comissao de reservas e loja', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id, 'valor_hora' => 100]);
    $comprador = User::factory()->create();
    $produto = Produto::factory()->create(['dono_id' => $dono->id, 'preco' => 50]);

    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => now()->startOfMonth()->addDays(2)->toDateString(),
        'hora_inicio' => '08:00:00',
        'hora_fim' => '09:00:00',
        'status' => ReservaStatus::Confirmada,
        'status_pagamento' => StatusPagamento::Pago,
    ]);

    criarPedidoParaFinanceiro($dono, $comprador, $produto);

    $resumo = Livewire::actingAs($dono)
        ->test(Financeiro::class)
        ->instance()
        ->resumoGeral;

    expect($resumo['faturamentoReservas'])->toBe(100.0)
        ->and($resumo['faturamentoLoja'])->toBe(50.0)
        ->and($resumo['faturamentoTotal'])->toBe(150.0)
        ->and($resumo['comissaoTotal'])->toBe(7.5)
        ->and($resumo['liquidoTotal'])->toBe(142.5);
});

test('transacoes unificadas trazem reservas e pedidos juntos, e o filtro de tipo isola cada um', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id]);
    $comprador = User::factory()->create();
    $produto = Produto::factory()->create(['dono_id' => $dono->id]);

    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => now()->startOfMonth()->addDays(2)->toDateString(),
        'status' => ReservaStatus::Confirmada,
        'status_pagamento' => StatusPagamento::Pago,
    ]);

    criarPedidoParaFinanceiro($dono, $comprador, $produto);

    $component = Livewire::actingAs($dono)->test(Financeiro::class);

    expect($component->instance()->transacoes->total())->toBe(2);

    $component->set('filtroTipo', 'reservas');
    expect($component->instance()->transacoes->total())->toBe(1)
        ->and($component->instance()->transacoes->first()['tipo'])->toBe('reserva');

    $component->set('filtroTipo', 'produtos');
    expect($component->instance()->transacoes->total())->toBe(1)
        ->and($component->instance()->transacoes->first()['tipo'])->toBe('produto');
});

test('pedido cancelado nao aparece nas transacoes nem no faturamento da loja', function () {
    $dono = User::factory()->donoQuadra()->create();
    $comprador = User::factory()->create();
    $produto = Produto::factory()->create(['dono_id' => $dono->id]);

    criarPedidoParaFinanceiro($dono, $comprador, $produto, status: PedidoStatus::Cancelado);

    $component = Livewire::actingAs($dono)->test(Financeiro::class);

    expect((float) $component->instance()->faturamentoLojaMes)->toBe(0.0)
        ->and($component->instance()->transacoes->total())->toBe(0);
});
