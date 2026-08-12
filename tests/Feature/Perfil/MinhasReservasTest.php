<?php

use App\Enums\ReservaStatus;
use App\Livewire\Perfil\MinhasReservas;
use App\Models\Quadra;
use App\Models\Reserva;
use App\Models\User;
use Livewire\Livewire;

test('página de perfil exige login', function () {
    $this->get(route('perfil'))->assertRedirect(route('login'));
});

test('minhas reservas separa reservas futuras de passadas', function () {
    $user = User::factory()->create();
    $quadra = Quadra::factory()->create(['nome' => 'Quadra Teste']);

    $futura = Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'user_id' => $user->id,
        'data' => now()->addDay()->toDateString(),
        'status' => ReservaStatus::Confirmada,
    ]);

    $passada = Reserva::factory()->create([
        'quadra_id' => $quadra->id,
        'user_id' => $user->id,
        'data' => now()->subDay()->toDateString(),
        'status' => ReservaStatus::Confirmada,
    ]);

    Livewire::actingAs($user)
        ->test(MinhasReservas::class)
        ->assertSet('futuras', fn ($futuras) => $futuras->pluck('id')->contains($futura->id) && ! $futuras->pluck('id')->contains($passada->id))
        ->assertSet('passadas', fn ($passadas) => $passadas->pluck('id')->contains($passada->id) && ! $passadas->pluck('id')->contains($futura->id));
});
