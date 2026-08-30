<?php

use App\Livewire\Perfil\Notificacoes;
use App\Models\Quadra;
use App\Models\Sala;
use App\Models\User;
use Livewire\Livewire;

test('organizador vê aviso para fechar a sala quando faltam menos de 5h e ainda há vagas', function () {
    $organizador = User::factory()->create();
    $quadra = Quadra::factory()->create(['nome' => 'Arena Teste', 'valor_hora' => 100]);
    $sala = Sala::factory()->create([
        'criador_id' => $organizador->id,
        'quadra_id' => $quadra->id,
        'max_participantes' => 10,
        'preco_pessoa' => null,
        'data' => now()->addHours(2)->toDateString(),
        'horario_inicio' => now()->addHours(2)->format('H:i:s'),
        'horario_fim' => now()->addHours(3)->format('H:i:s'),
    ]);
    $sala->participantes()->attach($organizador->id);

    Livewire::actingAs($organizador)
        ->test(Notificacoes::class)
        ->assertSee('Sua sala fecha em breve')
        ->assertSee('Arena Teste');
});

test('organizador não vê aviso de fechar sala quando faltam mais de 5h', function () {
    $organizador = User::factory()->create();
    $sala = Sala::factory()->create([
        'criador_id' => $organizador->id,
        'max_participantes' => 10,
        'data' => now()->addHours(10)->toDateString(),
        'horario_inicio' => now()->addHours(10)->format('H:i:s'),
        'horario_fim' => now()->addHours(11)->format('H:i:s'),
    ]);
    $sala->participantes()->attach($organizador->id);

    Livewire::actingAs($organizador)
        ->test(Notificacoes::class)
        ->assertDontSee('Sua sala fecha em breve');
});

test('organizador não vê aviso de fechar sala quando ela já está com todas as vagas preenchidas', function () {
    $organizador = User::factory()->create();
    $sala = Sala::factory()->create([
        'criador_id' => $organizador->id,
        'max_participantes' => 1,
        'data' => now()->addHours(2)->toDateString(),
        'horario_inicio' => now()->addHours(2)->format('H:i:s'),
        'horario_fim' => now()->addHours(3)->format('H:i:s'),
    ]);
    $sala->participantes()->attach($organizador->id);

    Livewire::actingAs($organizador)
        ->test(Notificacoes::class)
        ->assertDontSee('Sua sala fecha em breve');
});

test('usuário vê aviso de quadra com valor abaixo da média das quadras ativas', function () {
    $user = User::factory()->create();
    Quadra::factory()->create(['nome' => 'Quadra Cara', 'ativa' => true, 'valor_hora' => 200]);
    Quadra::factory()->create(['nome' => 'Quadra Cara 2', 'ativa' => true, 'valor_hora' => 200]);
    Quadra::factory()->create(['nome' => 'Quadra Barata', 'ativa' => true, 'valor_hora' => 50]);

    Livewire::actingAs($user)
        ->test(Notificacoes::class)
        ->assertSee('Quadra com valor abaixo da média')
        ->assertSee('Quadra Barata')
        ->assertDontSee('Quadra Cara');
});

test('quadra inativa não gera aviso de valor abaixo da média', function () {
    $user = User::factory()->create();
    Quadra::factory()->create(['ativa' => true, 'valor_hora' => 200]);
    Quadra::factory()->create(['nome' => 'Quadra Inativa Barata', 'ativa' => false, 'valor_hora' => 10]);

    Livewire::actingAs($user)
        ->test(Notificacoes::class)
        ->assertDontSee('Quadra Inativa Barata');
});

test('sem salas fechando e sem quadras baratas, a lista de notificações vem vazia', function () {
    $user = User::factory()->create();
    Quadra::factory()->create(['ativa' => true, 'valor_hora' => 100]);
    Quadra::factory()->create(['ativa' => true, 'valor_hora' => 100]);

    Livewire::actingAs($user)
        ->test(Notificacoes::class)
        ->assertSee('Nenhuma notificação por agora');
});
