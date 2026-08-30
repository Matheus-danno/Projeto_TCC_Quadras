<?php

use App\Enums\AceitacaoNivel;
use App\Enums\Esporte;
use App\Enums\NivelHabilidade;
use App\Enums\ReservaStatus;
use App\Livewire\Salas\Criar;
use App\Livewire\Salas\Detalhe;
use App\Livewire\Salas\Listagem;
use App\Livewire\Salas\Pagamento;
use App\Models\Quadra;
use App\Models\Reserva;
use App\Models\Sala;
use App\Models\User;
use Livewire\Livewire;

test('formulário de criar partida já vem com data, hora e nível preenchidos por padrão', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Criar::class);

    expect($component->get('data'))->toBe(now()->toDateString())
        ->and($component->get('horaInicio'))->not->toBe('')
        ->and($component->get('nivelDesejado'))->toBe(NivelHabilidade::Intermediario->value);
});

test('tentar criar sem escolher uma quadra mostra mensagem de erro amigável', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Criar::class)
        ->set('nome', 'Racha sem quadra')
        ->set('esporte', Esporte::Futebol->value)
        ->call('criar')
        ->assertHasErrors('quadraId')
        ->assertSee('Corrija os campos abaixo')
        ->assertSee('Quadra (seção "Escolha a quadra")');

    expect(Sala::count())->toBe(0);
});

test('usuário autenticado consegue criar uma sala/partida', function () {
    $user = User::factory()->create();
    $quadra = Quadra::factory()->create(['ativa' => true]);
    $data = now()->addDay()->toDateString();

    Livewire::actingAs($user)
        ->test(Criar::class)
        ->set('nome', 'Racha de sexta-feira')
        ->set('esporte', Esporte::Futebol->value)
        ->set('maxParticipantes', 10)
        ->set('quadraId', $quadra->id)
        ->set('data', $data)
        ->set('horaInicio', '20:00')
        ->set('duracaoMinutos', 90)
        ->set('nivelDesejado', NivelHabilidade::Intermediario->value)
        ->set('aceitacaoNiveis', AceitacaoNivel::Todos->value)
        ->call('criar')
        ->assertHasNoErrors();

    expect(Sala::count())->toBe(1);

    $sala = Sala::first();

    expect($sala->nome)->toBe('Racha de sexta-feira')
        ->and($sala->esporte)->toBe(Esporte::Futebol)
        ->and($sala->criador_id)->toBe($user->id)
        ->and($sala->max_participantes)->toBe(10)
        ->and($sala->quadra_id)->toBe($quadra->id)
        ->and($sala->nivel_desejado)->toBe(NivelHabilidade::Intermediario)
        ->and($sala->aceitacao_niveis_adjacentes)->toBe(AceitacaoNivel::Todos)
        ->and($sala->privada)->toBeFalse()
        ->and($sala->aprovacao_manual)->toBeFalse()
        ->and($sala->participantes->pluck('id')->all())->toBe([$user->id]);
});

test('partida criada aparece na listagem de encontre um time', function () {
    $user = User::factory()->create();
    $quadra = Quadra::factory()->create(['ativa' => true]);
    $data = now()->addDay()->toDateString();

    Livewire::actingAs($user)
        ->test(Criar::class)
        ->set('nome', 'Racha de sexta-feira')
        ->set('esporte', Esporte::Futebol->value)
        ->set('maxParticipantes', 10)
        ->set('quadraId', $quadra->id)
        ->set('data', $data)
        ->set('horaInicio', '20:00')
        ->set('duracaoMinutos', 90)
        ->set('nivelDesejado', NivelHabilidade::Intermediario->value)
        ->set('aceitacaoNiveis', AceitacaoNivel::Todos->value)
        ->call('criar')
        ->assertHasNoErrors();

    Livewire::actingAs($user)
        ->test(Listagem::class)
        ->assertSee('Racha de sexta-feira');
});

test('criar partida gera uma reserva vinculada que bloqueia o horário para outros agendamentos', function () {
    $user = User::factory()->create();
    $quadra = Quadra::factory()->create(['ativa' => true]);
    $data = now()->addDay()->toDateString();

    Livewire::actingAs($user)
        ->test(Criar::class)
        ->set('nome', 'Racha de sexta-feira')
        ->set('esporte', Esporte::Futebol->value)
        ->set('maxParticipantes', 10)
        ->set('quadraId', $quadra->id)
        ->set('data', $data)
        ->set('horaInicio', '20:00')
        ->set('duracaoMinutos', 90)
        ->set('nivelDesejado', NivelHabilidade::Intermediario->value)
        ->set('aceitacaoNiveis', AceitacaoNivel::Todos->value)
        ->call('criar')
        ->assertHasNoErrors();

    $sala = Sala::first();

    expect(Reserva::count())->toBe(1);

    $reserva = Reserva::first();

    expect($sala->reserva_id)->toBe($reserva->id)
        ->and($reserva->quadra_id)->toBe($quadra->id)
        ->and($reserva->status)->toBe(ReservaStatus::Confirmada)
        ->and(substr($reserva->hora_inicio, 0, 5))->toBe('20:00')
        ->and(substr($reserva->hora_fim, 0, 5))->toBe('21:30');
});

test('ajustar a quantidade de horas de uma quadra não afeta as demais quadras', function () {
    $user = User::factory()->create();
    $quadraA = Quadra::factory()->create(['ativa' => true]);
    $quadraB = Quadra::factory()->create(['ativa' => true]);

    $component = Livewire::actingAs($user)
        ->test(Criar::class)
        ->call('incrementarHoras', $quadraA->id)
        ->call('incrementarHoras', $quadraA->id);

    expect($component->instance()->horasPara($quadraA->id))->toBe(4)
        ->and($component->instance()->horasPara($quadraB->id))->toBe(2);
});

test('criação de partida é rejeitada quando o horário da quadra conflita com uma reserva existente', function () {
    $user = User::factory()->create();
    $quadra = Quadra::factory()->create(['ativa' => true]);
    $data = now()->addDay()->toDateString();

    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => $data,
        'hora_inicio' => '20:00:00',
        'hora_fim' => '21:00:00',
        'status' => ReservaStatus::Confirmada,
    ]);

    Livewire::actingAs($user)
        ->test(Criar::class)
        ->set('nome', 'Racha de sexta-feira')
        ->set('esporte', Esporte::Futebol->value)
        ->set('maxParticipantes', 10)
        ->set('quadraId', $quadra->id)
        ->set('data', $data)
        ->set('horaInicio', '20:30')
        ->set('duracaoMinutos', 60)
        ->set('nivelDesejado', NivelHabilidade::Intermediario->value)
        ->set('aceitacaoNiveis', AceitacaoNivel::Todos->value)
        ->call('criar')
        ->assertHasErrors('horaInicio');

    expect(Sala::count())->toBe(0)
        ->and(Reserva::count())->toBe(1);
});

test('jogador consegue entrar em uma sala com vagas disponíveis pagando via pix', function () {
    $criador = User::factory()->create();
    $jogador = User::factory()->create();

    $quadra = Quadra::factory()->create(['valor_hora' => 60]);
    $sala = Sala::factory()->create([
        'criador_id' => $criador->id,
        'quadra_id' => $quadra->id,
        'max_participantes' => 5,
        'duracao_minutos' => 60,
    ]);

    Livewire::actingAs($jogador)
        ->test(Pagamento::class, ['sala' => $sala])
        ->assertSet('formaPagamento', 'pix')
        ->call('confirmarPagamento')
        ->assertSet('erro', null)
        ->assertRedirect(route('salas.confirmacao', $sala));

    expect($sala->participantes()->pluck('users.id')->all())->toBe([$jogador->id]);

    $pivot = $sala->participantes()->first()->pivot;
    expect($pivot->forma_pagamento)->toBe('pix')
        ->and((float) $pivot->valor_pago)->toBe(12.0);
});

test('pagamento com cartão exige os dados do cartão preenchidos', function () {
    $criador = User::factory()->create();
    $jogador = User::factory()->create();
    $sala = Sala::factory()->create(['criador_id' => $criador->id, 'max_participantes' => 5]);

    Livewire::actingAs($jogador)
        ->test(Pagamento::class, ['sala' => $sala])
        ->call('selecionarFormaPagamento', 'cartao')
        ->call('confirmarPagamento')
        ->assertHasErrors(['numeroCartao', 'nomeCartao', 'validade', 'cvv']);

    expect($sala->participantes()->count())->toBe(0);
});

test('pagamento com cartão preenchido corretamente confirma a entrada na sala', function () {
    $criador = User::factory()->create();
    $jogador = User::factory()->create();
    $sala = Sala::factory()->create(['criador_id' => $criador->id, 'max_participantes' => 5]);

    Livewire::actingAs($jogador)
        ->test(Pagamento::class, ['sala' => $sala])
        ->call('selecionarFormaPagamento', 'cartao')
        ->set('numeroCartao', '4111111111111111')
        ->set('nomeCartao', 'Jogador Teste')
        ->set('validade', '12/30')
        ->set('cvv', '123')
        ->call('confirmarPagamento')
        ->assertHasNoErrors()
        ->assertRedirect(route('salas.confirmacao', $sala));

    expect($sala->participantes()->pluck('users.id')->all())->toBe([$jogador->id]);
});

test('visitante não autenticado não vê o botão de pagamento e é redirecionado ao tentar acessá-lo', function () {
    $criador = User::factory()->create();
    $sala = Sala::factory()->create(['criador_id' => $criador->id, 'nome' => 'Racha de domingo']);

    Livewire::test(Detalhe::class, ['sala' => $sala])
        ->assertSee('Racha de domingo')
        ->assertDontSee('Entrar e Pagar')
        ->assertSee('para participar');

    $this->get(route('salas.pagamento', $sala))->assertRedirect(route('login'));

    expect($sala->participantes()->count())->toBe(0);
});

test('entrada é rejeitada quando a sala está cheia', function () {
    $criador = User::factory()->create();
    $sala = Sala::factory()->create([
        'criador_id' => $criador->id,
        'max_participantes' => 1,
    ]);
    $sala->participantes()->attach($criador->id);

    $jogador = User::factory()->create();

    Livewire::actingAs($jogador)
        ->test(Pagamento::class, ['sala' => $sala])
        ->call('confirmarPagamento')
        ->assertSet('erro', 'Essa sala já está cheia.');

    expect($sala->participantes()->count())->toBe(1);
});

test('página de confirmação mostra a forma de pagamento e o valor pago', function () {
    $criador = User::factory()->create();
    $jogador = User::factory()->create();
    $quadra = Quadra::factory()->create(['valor_hora' => 60]);
    $sala = Sala::factory()->create([
        'criador_id' => $criador->id,
        'quadra_id' => $quadra->id,
        'max_participantes' => 5,
        'duracao_minutos' => 60,
    ]);

    $sala->participantes()->attach($jogador->id, ['forma_pagamento' => 'pix', 'valor_pago' => 12]);

    $this->actingAs($jogador)
        ->get(route('salas.confirmacao', $sala))
        ->assertOk()
        ->assertSee('Vaga confirmada!')
        ->assertSee('Pago via Pix')
        ->assertSee('12,00', escape: false);
});
