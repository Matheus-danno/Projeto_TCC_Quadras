<?php

use App\Enums\ReservaStatus;
use App\Exceptions\HorarioIndisponivelException;
use App\Models\Quadra;
use App\Models\Reserva;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

function reservaDireta(Quadra $quadra, string $data, string $inicio, string $fim, ReservaStatus $status = ReservaStatus::Confirmada): void
{
    DB::table('reservas')->insert([
        'quadra_id' => $quadra->id,
        'user_id' => User::factory()->create()->id,
        'data' => $data,
        'hora_inicio' => $inicio,
        'hora_fim' => $fim,
        'status' => $status->value,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

test('agendarSemConflito rejeita sobreposição parcial com reserva existente', function () {
    $quadra = Quadra::factory()->create();
    $data = now()->addDay()->toDateString();
    reservaDireta($quadra, $data, '19:00:00', '21:00:00');

    Reserva::agendarSemConflito($quadra, [
        'user_id' => User::factory()->create()->id,
        'data' => $data,
        'hora_inicio' => '20:00:00',
        'hora_fim' => '21:00:00',
        'status' => ReservaStatus::Confirmada,
    ]);
})->throws(HorarioIndisponivelException::class);

test('o banco barra a sobreposição mesmo gravando por fora da aplicação', function () {
    $quadra = Quadra::factory()->create();
    $data = now()->addDay()->toDateString();
    reservaDireta($quadra, $data, '19:00:00', '21:00:00');

    reservaDireta($quadra, $data, '20:00:00', '22:00:00');
})->throws(QueryException::class, 'reservas_sem_sobreposicao');

test('duas requisições que passam juntas pela verificação: a segunda vira erro de horário indisponível', function () {
    $quadra = Quadra::factory()->create();
    $data = now()->addDay()->toDateString();
    reservaDireta($quadra, $data, '10:00:00', '11:00:00');

    // Simula a corrida: a verificação da aplicação "não viu" a outra reserva,
    // então só a exclusion constraint do PostgreSQL impede a duplicidade.
    Reserva::agendarSemConflito($quadra, [
        'user_id' => User::factory()->create()->id,
        'data' => $data,
        'hora_inicio' => '10:00:00',
        'hora_fim' => '11:00:00',
        'status' => ReservaStatus::Pendente,
    ], fn () => true);
})->throws(HorarioIndisponivelException::class);

test('reserva encostada (uma termina às 20:00 e outra começa às 20:00) é permitida', function () {
    $quadra = Quadra::factory()->create();
    $data = now()->addDay()->toDateString();
    reservaDireta($quadra, $data, '19:00:00', '20:00:00');

    $reserva = Reserva::agendarSemConflito($quadra, [
        'user_id' => User::factory()->create()->id,
        'data' => $data,
        'hora_inicio' => '20:00:00',
        'hora_fim' => '21:00:00',
        'status' => ReservaStatus::Confirmada,
    ]);

    expect($reserva->exists)->toBeTrue()
        ->and(Reserva::count())->toBe(2);
});

test('reserva cancelada não bloqueia o horário', function () {
    $quadra = Quadra::factory()->create();
    $data = now()->addDay()->toDateString();
    reservaDireta($quadra, $data, '19:00:00', '20:00:00', ReservaStatus::Cancelada);

    reservaDireta($quadra, $data, '19:00:00', '20:00:00');

    expect(Reserva::count())->toBe(2);
});

test('mesmo horário em quadras diferentes é permitido', function () {
    $data = now()->addDay()->toDateString();

    reservaDireta(Quadra::factory()->create(), $data, '19:00:00', '20:00:00');
    reservaDireta(Quadra::factory()->create(), $data, '19:00:00', '20:00:00');

    expect(Reserva::count())->toBe(2);
});
