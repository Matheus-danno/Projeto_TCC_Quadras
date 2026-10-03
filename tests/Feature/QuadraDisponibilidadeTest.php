<?php

use App\Enums\ReservaStatus;
use App\Livewire\Quadras\Listagem;
use App\Livewire\Salas\Criar;
use App\Models\ExcecaoData;
use App\Models\Quadra;
use App\Models\Reserva;
use App\Models\User;
use Livewire\Livewire;

afterEach(function () {
    Carbon\Carbon::setTestNow();
});

test('dono sem horario de funcionamento configurado é tratado como aberto das 07h às 22h', function () {
    $dono = User::factory()->create(['horario_funcionamento' => null]);

    expect($dono->horarioFuncionamentoEm(now()->addDay()->toDateString()))
        ->toBe(['abertura' => '07:00', 'fechamento' => '22:00']);
});

test('horario de funcionamento configurado limita a janela de atendimento do dia', function () {
    $dono = User::factory()->create([
        'horario_funcionamento' => [
            'dias_uteis' => ['aberto' => true, 'inicio' => '09:00', 'fim' => '18:00'],
            'sabado' => ['aberto' => true, 'inicio' => '10:00', 'fim' => '14:00'],
            'domingo' => ['aberto' => false, 'inicio' => '09:00', 'fim' => '22:00'],
        ],
    ]);

    $segunda = Carbon\Carbon::parse('next monday')->toDateString();
    $sabado = Carbon\Carbon::parse('next saturday')->toDateString();
    $domingo = Carbon\Carbon::parse('next sunday')->toDateString();

    expect($dono->horarioFuncionamentoEm($segunda))->toBe(['abertura' => '09:00', 'fechamento' => '18:00'])
        ->and($dono->horarioFuncionamentoEm($sabado))->toBe(['abertura' => '10:00', 'fechamento' => '14:00'])
        ->and($dono->horarioFuncionamentoEm($domingo))->toBeNull();
});

test('excecao de data com fechamento total bloqueia a data mesmo dentro do horario normal', function () {
    $dono = User::factory()->create();
    $data = now()->addDay()->toDateString();

    ExcecaoData::create([
        'dono_id' => $dono->id,
        'data' => $data,
        'descricao' => 'Feriado',
        'fechado_dia_todo' => true,
    ]);

    expect($dono->horarioFuncionamentoEm($data))->toBeNull();
});

test('excecao de data com horario especial substitui a janela padrao', function () {
    $dono = User::factory()->create();
    $data = now()->addDay()->toDateString();

    ExcecaoData::create([
        'dono_id' => $dono->id,
        'data' => $data,
        'descricao' => 'Horário especial',
        'fechado_dia_todo' => false,
        'hora_abertura' => '14:00',
        'hora_fechamento' => '18:00',
    ]);

    expect($dono->horarioFuncionamentoEm($data))->toBe(['abertura' => '14:00', 'fechamento' => '18:00']);
});

test('dono em pausa fica indisponivel em qualquer data', function () {
    $dono = User::factory()->create(['pausa_ativa' => true, 'pausa_indeterminada' => true]);

    expect($dono->horarioFuncionamentoEm(now()->addDay()->toDateString()))->toBeNull();
});

test('horarioDisponivel da quadra rejeita horario fora da janela de funcionamento do dono', function () {
    $dono = User::factory()->create([
        'horario_funcionamento' => [
            'dias_uteis' => ['aberto' => true, 'inicio' => '09:00', 'fim' => '18:00'],
            'sabado' => ['aberto' => false, 'inicio' => '09:00', 'fim' => '18:00'],
            'domingo' => ['aberto' => false, 'inicio' => '09:00', 'fim' => '18:00'],
        ],
    ]);
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id]);
    $segunda = Carbon\Carbon::parse('next monday')->toDateString();

    expect($quadra->horarioDisponivel($segunda, '08:00:00', '09:00:00'))->toBeFalse()
        ->and($quadra->horarioDisponivel($segunda, '09:00:00', '10:00:00'))->toBeTrue()
        ->and($quadra->horarioDisponivel($segunda, '17:00:00', '19:00:00'))->toBeFalse();
});

test('horariosLivres exclui horarios fora da janela e horarios ja reservados', function () {
    $dono = User::factory()->create([
        'horario_funcionamento' => [
            'dias_uteis' => ['aberto' => true, 'inicio' => '09:00', 'fim' => '12:00'],
            'sabado' => ['aberto' => true, 'inicio' => '09:00', 'fim' => '12:00'],
            'domingo' => ['aberto' => true, 'inicio' => '09:00', 'fim' => '12:00'],
        ],
    ]);
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id]);
    $data = now()->addDay()->toDateString();

    Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'data' => $data,
        'hora_inicio' => '10:00:00',
        'hora_fim' => '11:00:00',
        'status' => ReservaStatus::Confirmada,
    ]);

    expect($quadra->horariosLivres($data, 60))->toBe(['09:00', '11:00']);
});

test('horariosLivres retorna vazio quando o dono esta pausado', function () {
    $dono = User::factory()->create(['pausa_ativa' => true, 'pausa_indeterminada' => true]);
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id]);

    expect($quadra->horariosLivres(now()->addDay()->toDateString()))->toBe([]);
});

test('tela de agendar desabilita horarios fora da janela de funcionamento da quadra selecionada', function () {
    $dono = User::factory()->create([
        'horario_funcionamento' => [
            'dias_uteis' => ['aberto' => true, 'inicio' => '09:00', 'fim' => '12:00'],
            'sabado' => ['aberto' => true, 'inicio' => '09:00', 'fim' => '12:00'],
            'domingo' => ['aberto' => true, 'inicio' => '09:00', 'fim' => '12:00'],
        ],
    ]);
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id]);
    $jogador = User::factory()->create();
    $data = now()->addDay()->toDateString();

    $component = Livewire::actingAs($jogador)
        ->test(Listagem::class)
        ->call('selecionarQuadra', $quadra->id)
        ->set('data', $data);

    expect($component->instance()->horariosLivresQuadraSelecionada())
        ->toBe(['09:00', '10:00', '11:00']);
});

test('reserva fora da janela de funcionamento e rejeitada mesmo se o horario for forcado no componente', function () {
    $dono = User::factory()->create([
        'horario_funcionamento' => [
            'dias_uteis' => ['aberto' => true, 'inicio' => '09:00', 'fim' => '12:00'],
            'sabado' => ['aberto' => true, 'inicio' => '09:00', 'fim' => '12:00'],
            'domingo' => ['aberto' => true, 'inicio' => '09:00', 'fim' => '12:00'],
        ],
    ]);
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id]);
    $jogador = User::factory()->create();
    $data = now()->addDay()->toDateString();

    Livewire::actingAs($jogador)
        ->test(Listagem::class)
        ->call('selecionarQuadra', $quadra->id)
        ->set('data', $data)
        ->set('horaInicio', '20:00')
        ->call('reservar')
        ->assertHasErrors('horaInicio');

    expect(Reserva::count())->toBe(0);
});

test('criar sala desabilita horarios em que nenhuma quadra do esporte esta disponivel', function () {
    $donoAberto = User::factory()->create([
        'horario_funcionamento' => [
            'dias_uteis' => ['aberto' => true, 'inicio' => '09:00', 'fim' => '12:00'],
            'sabado' => ['aberto' => true, 'inicio' => '09:00', 'fim' => '12:00'],
            'domingo' => ['aberto' => true, 'inicio' => '09:00', 'fim' => '12:00'],
        ],
    ]);
    $donoFechado = User::factory()->create([
        'horario_funcionamento' => [
            'dias_uteis' => ['aberto' => false, 'inicio' => '09:00', 'fim' => '12:00'],
            'sabado' => ['aberto' => false, 'inicio' => '09:00', 'fim' => '12:00'],
            'domingo' => ['aberto' => false, 'inicio' => '09:00', 'fim' => '12:00'],
        ],
    ]);
    Quadra::factory()->create(['dono_id' => $donoAberto->id, 'esporte' => App\Enums\Esporte::Futebol->value, 'ativa' => true]);
    Quadra::factory()->create(['dono_id' => $donoFechado->id, 'esporte' => App\Enums\Esporte::Futebol->value, 'ativa' => true]);

    $user = User::factory()->create();
    $data = now()->addDay()->toDateString();

    $component = Livewire::actingAs($user)
        ->test(Criar::class)
        ->set('esporte', App\Enums\Esporte::Futebol->value)
        ->set('data', $data)
        ->set('duracaoMinutos', 60);

    expect($component->instance()->horariosComQuadraDisponivel())
        ->toBe(['09:00', '10:00', '11:00']);
});

test('horariosLivres exclui horarios de hoje que ja passaram', function () {
    Carbon\Carbon::setTestNow(Carbon\Carbon::today('America/Sao_Paulo')->setTime(14, 30));

    $quadra = Quadra::factory()->create(['dono_id' => User::factory()->create(['horario_funcionamento' => null])->id]);
    $hoje = now()->toDateString();

    $livres = $quadra->horariosLivres($hoje, 60);

    expect($livres)->not->toContain('07:00')
        ->and($livres)->not->toContain('14:00')
        ->and($livres)->toContain('15:00');
});

test('horarioDisponivel rejeita horario de hoje que ja passou mesmo dentro da janela de funcionamento', function () {
    Carbon\Carbon::setTestNow(Carbon\Carbon::today('America/Sao_Paulo')->setTime(14, 30));

    $quadra = Quadra::factory()->create(['dono_id' => User::factory()->create(['horario_funcionamento' => null])->id]);
    $hoje = now()->toDateString();

    expect($quadra->horarioDisponivel($hoje, '14:00:00', '15:00:00'))->toBeFalse()
        ->and($quadra->horarioDisponivel($hoje, '15:00:00', '16:00:00'))->toBeTrue();
});

test('tela de agendar desabilita horarios de hoje que ja passaram', function () {
    Carbon\Carbon::setTestNow(Carbon\Carbon::today('America/Sao_Paulo')->setTime(14, 30));

    $dono = User::factory()->create(['horario_funcionamento' => null]);
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id]);
    $jogador = User::factory()->create();
    $hoje = now()->toDateString();

    $component = Livewire::actingAs($jogador)
        ->test(Listagem::class)
        ->call('selecionarQuadra', $quadra->id)
        ->set('data', $hoje);

    expect($component->instance()->horariosLivresQuadraSelecionada())
        ->not->toContain('08:00')
        ->toContain('15:00');
});

test('reservar horario de hoje que ja passou é rejeitado com mensagem de erro', function () {
    Carbon\Carbon::setTestNow(Carbon\Carbon::today('America/Sao_Paulo')->setTime(14, 30));

    $user = User::factory()->create();
    $quadra = Quadra::factory()->create(['dono_id' => User::factory()->create(['horario_funcionamento' => null])->id]);
    $hoje = now()->toDateString();

    Livewire::actingAs($user)
        ->test(Listagem::class)
        ->call('selecionarQuadra', $quadra->id)
        ->set('data', $hoje)
        ->set('horaInicio', '08:00')
        ->call('reservar')
        ->assertHasErrors('horaInicio');

    expect(Reserva::count())->toBe(0);
});
