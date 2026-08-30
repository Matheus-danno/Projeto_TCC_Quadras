<?php

use App\Livewire\Quadras\Proximas;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

function fakeOverpass(array $elementos): void
{
    Http::fake([
        config('services.overpass.url') => Http::response(['elements' => $elementos]),
    ]);
}

test('busca por localização exibe as quadras ordenadas da mais próxima para a mais distante', function () {
    fakeOverpass([
        ['type' => 'node', 'tags' => ['name' => 'Quadra Mais Distante', 'sport' => 'soccer'], 'lat' => -8.0676, 'lon' => -34.8770],
        ['type' => 'node', 'tags' => ['name' => 'Quadra Mais Perto', 'sport' => 'basketball'], 'lat' => -8.0486, 'lon' => -34.8770],
        ['type' => 'node', 'tags' => ['name' => 'Quadra no Meio', 'sport' => 'tennis'], 'lat' => -8.0576, 'lon' => -34.8770],
    ]);

    Livewire::test(Proximas::class)
        ->call('buscarPorLocalizacao', -8.0476, -34.8770)
        ->assertSeeInOrder(['Quadra Mais Perto', 'Quadra no Meio', 'Quadra Mais Distante']);
});

test('elementos sem a tag name são descartados da lista', function () {
    fakeOverpass([
        ['type' => 'node', 'tags' => ['name' => 'Quadra com Nome', 'sport' => 'soccer'], 'lat' => -8.05, 'lon' => -34.88],
        ['type' => 'node', 'tags' => ['sport' => 'soccer'], 'lat' => -8.06, 'lon' => -34.89],
    ]);

    Livewire::test(Proximas::class)
        ->call('buscarPorLocalizacao', -8.0476, -34.8770)
        ->assertSee('Quadra com Nome')
        ->assertCount('quadras', 1);
});

test('erro da overpass api não quebra o componente e expõe mensagem amigável', function () {
    Http::fake([
        config('services.overpass.url') => Http::response('Erro interno', 500),
    ]);

    Livewire::test(Proximas::class)
        ->call('buscarPorLocalizacao', -8.0476, -34.8770)
        ->assertSet('erro', 'Não foi possível buscar quadras próximas agora. Tente novamente em alguns instantes.')
        ->assertSee('Não foi possível buscar quadras próximas agora');
});

test('trocar o raio refaz a busca com o novo raio em metros', function () {
    fakeOverpass([]);

    Livewire::test(Proximas::class)
        ->call('buscarPorLocalizacao', -8.0476, -34.8770)
        ->call('atualizarRaio', 10);

    Http::assertSentCount(2);

    Http::assertSent(fn (Request $request) => str_contains($request['data'], 'around:5000,'));
    Http::assertSent(fn (Request $request) => str_contains($request['data'], 'around:10000,'));
});

test('sem localização definida a lista de quadras vem vazia e nenhuma chamada http é feita', function () {
    Http::fake();

    Livewire::test(Proximas::class)
        ->assertCount('quadras', 0)
        ->assertSee('Usar minha localização');

    Http::assertNothingSent();
});
