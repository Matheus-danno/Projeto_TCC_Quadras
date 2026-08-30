<?php

use App\Livewire\Perfil\MinhasSalas;
use App\Models\Sala;
use App\Models\User;
use Livewire\Livewire;

test('minhas salas separa salas futuras de passadas e inclui salas sem data definida entre as futuras', function () {
    $user = User::factory()->create();

    $futura = Sala::factory()->create(['data' => now()->addDay()->toDateString()]);
    $futura->participantes()->attach($user->id);

    $semData = Sala::factory()->create(['data' => null, 'horario_inicio' => null, 'horario_fim' => null]);
    $semData->participantes()->attach($user->id);

    $passada = Sala::factory()->create(['data' => now()->subDay()->toDateString()]);
    $passada->participantes()->attach($user->id);

    Livewire::actingAs($user)
        ->test(MinhasSalas::class)
        ->assertSet('futuras', fn ($futuras) => $futuras->pluck('id')->contains($futura->id)
            && $futuras->pluck('id')->contains($semData->id)
            && ! $futuras->pluck('id')->contains($passada->id))
        ->assertSet('passadas', fn ($passadas) => $passadas->pluck('id')->contains($passada->id)
            && ! $passadas->pluck('id')->contains($futura->id));
});

test('sala aparece com selo de organizador para quem a criou', function () {
    $organizador = User::factory()->create();
    $sala = Sala::factory()->create(['criador_id' => $organizador->id, 'data' => now()->addDay()->toDateString()]);
    $sala->participantes()->attach($organizador->id);

    Livewire::actingAs($organizador)
        ->test(MinhasSalas::class)
        ->assertSee('Organizador');
});
