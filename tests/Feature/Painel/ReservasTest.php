<?php

use App\Enums\ReservaStatus;
use App\Livewire\Painel\Reservas\Listagem;
use App\Models\Conversa;
use App\Models\Quadra;
use App\Models\Reserva;
use App\Models\User;
use Livewire\Livewire;

test('dono vê apenas reservas das próprias quadras', function () {
    $dono = User::factory()->donoQuadra()->create();
    $outroDono = User::factory()->donoQuadra()->create();

    $quadraDono = Quadra::factory()->create(['dono_id' => $dono->id, 'nome' => 'Quadra do Dono']);
    $quadraOutro = Quadra::factory()->create(['dono_id' => $outroDono->id, 'nome' => 'Quadra de Outro Dono']);

    Reserva::factory()->create(['quadra_id' => $quadraDono->id]);
    Reserva::factory()->create(['quadra_id' => $quadraOutro->id]);

    Livewire::actingAs($dono)
        ->test(Listagem::class)
        ->assertSee('Quadra do Dono')
        ->assertDontSee('Quadra de Outro Dono');
});

test('dono confirma uma reserva pendente', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id]);
    $reserva = Reserva::factory()->create(['quadra_id' => $quadra->id, 'status' => ReservaStatus::Pendente]);

    Livewire::actingAs($dono)
        ->test(Listagem::class)
        ->call('confirmar', $reserva->id);

    expect($reserva->fresh()->status)->toBe(ReservaStatus::Confirmada);
});

test('confirmar não altera reserva que já não está pendente', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id]);
    $reserva = Reserva::factory()->create(['quadra_id' => $quadra->id, 'status' => ReservaStatus::Cancelada]);

    Livewire::actingAs($dono)
        ->test(Listagem::class)
        ->call('confirmar', $reserva->id);

    expect($reserva->fresh()->status)->toBe(ReservaStatus::Cancelada);
});

test('dono cancela uma reserva pendente ou confirmada', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id]);
    $reserva = Reserva::factory()->create(['quadra_id' => $quadra->id, 'status' => ReservaStatus::Confirmada]);

    Livewire::actingAs($dono)
        ->test(Listagem::class)
        ->call('pedirCancelamento', $reserva->id)
        ->call('cancelar');

    expect($reserva->fresh()->status)->toBe(ReservaStatus::Cancelada);
});

test('cancelar uma reserva confirmada credita o valor da quadra ao cliente automaticamente', function () {
    $dono = User::factory()->donoQuadra()->create();
    $cliente = User::factory()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id, 'valor_hora' => 90]);
    $reserva = Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'user_id' => $cliente->id,
        'status' => ReservaStatus::Confirmada,
    ]);

    Livewire::actingAs($dono)
        ->test(Listagem::class)
        ->call('pedirCancelamento', $reserva->id)
        ->set('motivoCancelamento', 'Manutenção na quadra.')
        ->call('cancelar');

    expect($reserva->fresh()->status)->toBe(ReservaStatus::Cancelada)
        ->and($reserva->fresh()->cancelamento_tipo)->toBe('credito')
        ->and($reserva->fresh()->motivo_cancelamento)->toBe('Manutenção na quadra.')
        ->and((float) $cliente->fresh()->saldo_creditos)->toBe(90.0);
});

test('cancelar uma reserva pendente não gera credito', function () {
    $dono = User::factory()->donoQuadra()->create();
    $cliente = User::factory()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id, 'valor_hora' => 90]);
    $reserva = Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'user_id' => $cliente->id,
        'status' => ReservaStatus::Pendente,
    ]);

    Livewire::actingAs($dono)
        ->test(Listagem::class)
        ->call('pedirCancelamento', $reserva->id)
        ->call('cancelar');

    expect($reserva->fresh()->status)->toBe(ReservaStatus::Cancelada)
        ->and($reserva->fresh()->cancelamento_tipo)->toBeNull()
        ->and((float) $cliente->fresh()->saldo_creditos)->toBe(0.0);
});

test('cancelar notificando o cliente cria uma mensagem na conversa', function () {
    $dono = User::factory()->donoQuadra()->create();
    $cliente = User::factory()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id]);
    $reserva = Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'user_id' => $cliente->id,
        'status' => ReservaStatus::Confirmada,
    ]);

    Livewire::actingAs($dono)
        ->test(Listagem::class)
        ->call('pedirCancelamento', $reserva->id)
        ->set('notificarCliente', true)
        ->call('cancelar');

    $conversa = Conversa::where('quadra_id', $quadra->id)->where('jogador_id', $cliente->id)->first();

    expect($conversa)->not->toBeNull()
        ->and($conversa->mensagens)->toHaveCount(1);
});

test('cancelar sem marcar notificar cliente não cria mensagem', function () {
    $dono = User::factory()->donoQuadra()->create();
    $cliente = User::factory()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id]);
    $reserva = Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'user_id' => $cliente->id,
        'status' => ReservaStatus::Confirmada,
    ]);

    Livewire::actingAs($dono)
        ->test(Listagem::class)
        ->call('pedirCancelamento', $reserva->id)
        ->set('notificarCliente', false)
        ->call('cancelar');

    expect(Conversa::where('quadra_id', $quadra->id)->where('jogador_id', $cliente->id)->exists())->toBeFalse();
});

test('dono envia mensagem para o cliente a partir dos detalhes da reserva', function () {
    $dono = User::factory()->donoQuadra()->create();
    $cliente = User::factory()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id]);
    $reserva = Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'user_id' => $cliente->id,
        'status' => ReservaStatus::Confirmada,
    ]);

    Livewire::actingAs($dono)
        ->test(Listagem::class)
        ->call('verDetalhes', $reserva->id)
        ->set('novaMensagem', 'Olá, tudo certo para o seu horário.')
        ->call('enviarMensagem')
        ->assertHasNoErrors();

    $conversa = Conversa::where('quadra_id', $quadra->id)->where('jogador_id', $cliente->id)->first();

    expect($conversa)->not->toBeNull()
        ->and($conversa->mensagens->first()->texto)->toBe('Olá, tudo certo para o seu horário.');
});

test('enviar mensagem vazia é rejeitado', function () {
    $dono = User::factory()->donoQuadra()->create();
    $cliente = User::factory()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id]);
    $reserva = Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'user_id' => $cliente->id,
        'status' => ReservaStatus::Confirmada,
    ]);

    Livewire::actingAs($dono)
        ->test(Listagem::class)
        ->call('verDetalhes', $reserva->id)
        ->set('novaMensagem', '')
        ->call('enviarMensagem')
        ->assertHasErrors(['novaMensagem']);
});

test('dono vê os detalhes de uma reserva das próprias quadras', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id]);
    $reserva = Reserva::factory()->create(['quadra_id' => $quadra->id, 'status' => ReservaStatus::Confirmada]);

    $component = Livewire::actingAs($dono)
        ->test(Listagem::class)
        ->call('verDetalhes', $reserva->id);

    expect($component->get('reservaSelecionadaId'))->toBe($reserva->id)
        ->and($component->instance()->reservaSelecionada->id)->toBe($reserva->id);
});

test('dono não consegue confirmar, ver detalhes ou cancelar reserva de quadra que não é dele', function () {
    $dono = User::factory()->donoQuadra()->create();
    $outroDono = User::factory()->donoQuadra()->create();
    $quadraAlheia = Quadra::factory()->create(['dono_id' => $outroDono->id]);
    $reserva = Reserva::factory()->create(['quadra_id' => $quadraAlheia->id, 'status' => ReservaStatus::Pendente]);

    Livewire::actingAs($dono)
        ->test(Listagem::class)
        ->call('confirmar', $reserva->id)
        ->assertForbidden();

    Livewire::actingAs($dono)
        ->test(Listagem::class)
        ->call('verDetalhes', $reserva->id)
        ->assertForbidden();

    Livewire::actingAs($dono)
        ->test(Listagem::class)
        ->call('pedirCancelamento', $reserva->id)
        ->assertForbidden();

    expect($reserva->fresh()->status)->toBe(ReservaStatus::Pendente);
});

test('filtro por status mostra apenas reservas correspondentes', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id]);

    $pendente = Reserva::factory()->create(['quadra_id' => $quadra->id, 'status' => ReservaStatus::Pendente]);
    Reserva::factory()->create(['quadra_id' => $quadra->id, 'status' => ReservaStatus::Cancelada]);

    $reservas = Livewire::actingAs($dono)
        ->test(Listagem::class)
        ->set('status', ReservaStatus::Pendente->value)
        ->instance()
        ->reservas;

    expect($reservas)->toHaveCount(1)
        ->and($reservas->first()->id)->toBe($pendente->id);
});

test('aba hoje mostra apenas reservas do dia atual', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id]);

    $reservaHoje = Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => now()->toDateString(),
    ]);

    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => now()->addDays(3)->toDateString(),
    ]);

    $reservas = Livewire::actingAs($dono)
        ->test(Listagem::class)
        ->set('aba', 'hoje')
        ->instance()
        ->reservas;

    expect($reservas)->toHaveCount(1)
        ->and($reservas->first()->id)->toBe($reservaHoje->id);
});

test('aba semana mostra apenas reservas da semana atual', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id]);

    $inicioSemana = now()->startOfWeek();

    $reservaNaSemana = Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => $inicioSemana->copy()->addDays(2)->toDateString(),
    ]);

    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => $inicioSemana->copy()->subDays(3)->toDateString(),
    ]);

    $reservas = Livewire::actingAs($dono)
        ->test(Listagem::class)
        ->set('aba', 'semana')
        ->instance()
        ->reservas;

    expect($reservas)->toHaveCount(1)
        ->and($reservas->first()->id)->toBe($reservaNaSemana->id);
});

test('aba pendentes mostra apenas reservas pendentes', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id]);

    $pendente = Reserva::factory()->create(['quadra_id' => $quadra->id, 'status' => ReservaStatus::Pendente]);
    Reserva::factory()->create(['quadra_id' => $quadra->id, 'status' => ReservaStatus::Confirmada]);

    $reservas = Livewire::actingAs($dono)
        ->test(Listagem::class)
        ->set('aba', 'pendentes')
        ->instance()
        ->reservas;

    expect($reservas)->toHaveCount(1)
        ->and($reservas->first()->id)->toBe($pendente->id);
});

test('aba concluídas mostra reservas confirmadas com data já passada', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id]);

    $concluida = Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'status' => ReservaStatus::Confirmada,
        'data' => now()->subDays(2)->toDateString(),
    ]);

    // Confirmada, mas futura: ainda não é "concluída".
    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'status' => ReservaStatus::Confirmada,
        'data' => now()->addDays(2)->toDateString(),
    ]);

    // Passada, mas pendente: não é "concluída".
    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'status' => ReservaStatus::Pendente,
        'data' => now()->subDays(2)->toDateString(),
    ]);

    $reservas = Livewire::actingAs($dono)
        ->test(Listagem::class)
        ->set('aba', 'concluidas')
        ->instance()
        ->reservas;

    expect($reservas)->toHaveCount(1)
        ->and($reservas->first()->id)->toBe($concluida->id);
});

test('resumo calcula reservas de hoje, da semana, pendentes e faturamento da semana', function () {
    $dono = User::factory()->donoQuadra()->create();
    $outroDono = User::factory()->donoQuadra()->create();

    $quadra = Quadra::factory()->create(['dono_id' => $dono->id, 'valor_hora' => 100]);
    $quadraOutroDono = Quadra::factory()->create(['dono_id' => $outroDono->id, 'valor_hora' => 999]);

    // Hoje, confirmada, 2h: entra em hoje, semana e faturamento (100 * 2 = 200).
    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => now()->toDateString(),
        'hora_inicio' => '08:00:00',
        'hora_fim' => '10:00:00',
        'status' => ReservaStatus::Confirmada,
    ]);

    // Hoje, pendente: entra em hoje, semana e pendentes, mas não no faturamento.
    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => now()->toDateString(),
        'hora_inicio' => '11:00:00',
        'hora_fim' => '12:00:00',
        'status' => ReservaStatus::Pendente,
    ]);

    // Fora da semana atual: não entra em nenhum indicador.
    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => now()->startOfWeek()->subDays(3)->toDateString(),
        'status' => ReservaStatus::Confirmada,
    ]);

    // Reserva de outro dono: não deve contar em nada.
    Reserva::factory()->create([
        'quadra_id' => $quadraOutroDono->id,
        'data' => now()->toDateString(),
        'status' => ReservaStatus::Confirmada,
    ]);

    $resumo = Livewire::actingAs($dono)
        ->test(Listagem::class)
        ->instance()
        ->resumo;

    expect($resumo['hoje'])->toBe(2)
        ->and($resumo['semana'])->toBe(2)
        ->and($resumo['pendentes'])->toBe(1)
        ->and((float) $resumo['faturamentoSemana'])->toBe(200.0);
});
