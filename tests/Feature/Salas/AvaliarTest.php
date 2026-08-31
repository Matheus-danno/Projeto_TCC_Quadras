<?php

use App\Livewire\Salas\Avaliar;
use App\Models\Avaliacao;
use App\Models\AvaliacaoQuadra;
use App\Models\Quadra;
use App\Models\Sala;
use App\Models\User;
use Livewire\Livewire;

function criarSalaPassada(array $atributos = []): Sala
{
    return Sala::factory()->create(array_merge([
        'quadra_id' => Quadra::factory(),
        'data' => now()->subDays(2)->toDateString(),
    ], $atributos));
}

test('participante consegue acessar a avaliação de uma sala já ocorrida', function () {
    $jogador = User::factory()->create();
    $sala = criarSalaPassada();
    $sala->participantes()->attach($jogador->id);

    Livewire::actingAs($jogador)
        ->test(Avaliar::class, ['sala' => $sala])
        ->assertOk();
});

test('usuário que não participou da sala não consegue avaliar', function () {
    $jogador = User::factory()->create();
    $sala = criarSalaPassada();

    Livewire::actingAs($jogador)
        ->test(Avaliar::class, ['sala' => $sala])
        ->assertForbidden();
});

test('sala cujo jogo ainda não aconteceu não pode ser avaliada', function () {
    $jogador = User::factory()->create();
    $sala = Sala::factory()->create([
        'quadra_id' => Quadra::factory(),
        'data' => now()->addDays(2)->toDateString(),
    ]);
    $sala->participantes()->attach($jogador->id);

    Livewire::actingAs($jogador)
        ->test(Avaliar::class, ['sala' => $sala])
        ->assertForbidden();
});

test('jogador consegue avaliar o administrador da sala', function () {
    $admin = User::factory()->create();
    $jogador = User::factory()->create();
    $sala = criarSalaPassada(['criador_id' => $admin->id]);
    $sala->participantes()->attach([$admin->id, $jogador->id]);

    Livewire::actingAs($jogador)
        ->test(Avaliar::class, ['sala' => $sala])
        ->set('nota', 4)
        ->set('comentario', 'Ótimo organizador')
        ->call('avaliarAdministrador')
        ->assertHasNoErrors();

    expect(Avaliacao::count())->toBe(1);

    $avaliacao = Avaliacao::first();

    expect($avaliacao->sala_id)->toBe($sala->id)
        ->and($avaliacao->avaliado_id)->toBe($admin->id)
        ->and($avaliacao->autor_id)->toBe($jogador->id)
        ->and($avaliacao->nota)->toBe(4)
        ->and($avaliacao->comentario)->toBe('Ótimo organizador');

    expect($admin->fresh()->notaMedia())->toBe(4.0);
});

test('administrador não consegue avaliar a si mesmo', function () {
    $admin = User::factory()->create();
    $sala = criarSalaPassada(['criador_id' => $admin->id]);
    $sala->participantes()->attach($admin->id);

    Livewire::actingAs($admin)
        ->test(Avaliar::class, ['sala' => $sala])
        ->set('nota', 5)
        ->call('avaliarAdministrador')
        ->assertForbidden();

    expect(Avaliacao::count())->toBe(0);
});

test('jogador consegue avaliar a quadra da sala', function () {
    $jogador = User::factory()->create();
    $quadra = Quadra::factory()->create(['nome' => 'Arena Teste']);
    $sala = criarSalaPassada(['quadra_id' => $quadra->id]);
    $sala->participantes()->attach($jogador->id);

    Livewire::actingAs($jogador)
        ->test(Avaliar::class, ['sala' => $sala])
        ->set('nota', 5)
        ->set('comentario', 'Quadra muito boa')
        ->call('avaliarQuadra')
        ->assertHasNoErrors();

    expect(AvaliacaoQuadra::count())->toBe(1);

    $avaliacao = AvaliacaoQuadra::first();

    expect($avaliacao->sala_id)->toBe($sala->id)
        ->and($avaliacao->quadra_id)->toBe($quadra->id)
        ->and($avaliacao->autor_id)->toBe($jogador->id)
        ->and($avaliacao->nota)->toBe(5);

    expect($quadra->fresh()->notaMedia())->toBe(5.0);
});

test('jogador consegue avaliar outro participante do grupo', function () {
    $jogador = User::factory()->create();
    $outroJogador = User::factory()->create();
    $sala = criarSalaPassada();
    $sala->participantes()->attach([$jogador->id, $outroJogador->id]);

    Livewire::actingAs($jogador)
        ->test(Avaliar::class, ['sala' => $sala])
        ->set('nota', 3)
        ->call('avaliarParticipante', $outroJogador->id)
        ->assertHasNoErrors();

    expect(Avaliacao::count())->toBe(1);

    $avaliacao = Avaliacao::first();

    expect($avaliacao->avaliado_id)->toBe($outroJogador->id)
        ->and($avaliacao->autor_id)->toBe($jogador->id)
        ->and($avaliacao->nota)->toBe(3);
});

test('jogador não consegue avaliar alguém que não participou da sala', function () {
    $jogador = User::factory()->create();
    $estranho = User::factory()->create();
    $sala = criarSalaPassada();
    $sala->participantes()->attach($jogador->id);

    Livewire::actingAs($jogador)
        ->test(Avaliar::class, ['sala' => $sala])
        ->set('nota', 3)
        ->call('avaliarParticipante', $estranho->id)
        ->assertForbidden();

    expect(Avaliacao::count())->toBe(0);
});

test('lista de participantes para avaliar exclui o próprio usuário e o administrador', function () {
    $admin = User::factory()->create();
    $jogador = User::factory()->create();
    $outroJogador = User::factory()->create();
    $sala = criarSalaPassada(['criador_id' => $admin->id]);
    $sala->participantes()->attach([$admin->id, $jogador->id, $outroJogador->id]);

    $component = Livewire::actingAs($jogador)->test(Avaliar::class, ['sala' => $sala]);

    $ids = $component->instance()->participantesParaAvaliar()->pluck('id');

    expect($ids)->not->toContain($jogador->id)
        ->and($ids)->not->toContain($admin->id)
        ->and($ids)->toContain($outroJogador->id);
});

test('reenviar avaliação atualiza a nota existente em vez de duplicar', function () {
    $admin = User::factory()->create();
    $jogador = User::factory()->create();
    $sala = criarSalaPassada(['criador_id' => $admin->id]);
    $sala->participantes()->attach([$admin->id, $jogador->id]);

    $component = Livewire::actingAs($jogador)->test(Avaliar::class, ['sala' => $sala]);

    $component->set('nota', 2)->call('avaliarAdministrador');
    $component->set('nota', 5)->set('comentario', 'Revi minha opinião')->call('avaliarAdministrador');

    expect(Avaliacao::count())->toBe(1);

    $avaliacao = Avaliacao::first();

    expect($avaliacao->nota)->toBe(5)
        ->and($avaliacao->comentario)->toBe('Revi minha opinião');
});

test('avaliação exige nota entre 1 e 5', function () {
    $jogador = User::factory()->create();
    $quadra = Quadra::factory()->create();
    $sala = criarSalaPassada(['quadra_id' => $quadra->id]);
    $sala->participantes()->attach($jogador->id);

    Livewire::actingAs($jogador)
        ->test(Avaliar::class, ['sala' => $sala])
        ->set('nota', 6)
        ->call('avaliarQuadra')
        ->assertHasErrors('nota');

    expect(AvaliacaoQuadra::count())->toBe(0);
});
