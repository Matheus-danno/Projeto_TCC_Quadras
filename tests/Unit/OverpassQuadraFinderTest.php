<?php

use App\Services\Overpass\OverpassClient;
use App\Services\Overpass\OverpassQuadraFinder;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;

function finderComElementos(array $elementos): OverpassQuadraFinder
{
    $client = new class($elementos) extends OverpassClient
    {
        public function __construct(private readonly array $elementos) {}

        public function buscar(float $lat, float $lon, int $raioMetros): array
        {
            return $this->elementos;
        }
    };

    return new OverpassQuadraFinder($client, new Repository(new ArrayStore));
}

test('calcula a distância até cada quadra e ordena a lista da mais próxima para a mais distante', function () {
    $finder = finderComElementos([
        ['tags' => ['name' => 'Quadra Distante', 'sport' => 'soccer'], 'lat' => -8.1476, 'lon' => -34.8770],
        ['tags' => ['name' => 'Quadra Perto', 'sport' => 'soccer'], 'lat' => -8.0576, 'lon' => -34.8770],
    ]);

    $quadras = $finder->buscar(-8.0476, -34.8770, 5000);

    expect($quadras)->toHaveCount(2)
        ->and($quadras->first()->nome)->toBe('Quadra Perto')
        ->and($quadras->last()->nome)->toBe('Quadra Distante');

    // Diferença de 0,01 grau de latitude equivale a ~1.111,9 m (mesma longitude).
    expect($quadras->first()->distanciaMetros)->toBeGreaterThan(1100.0)
        ->and($quadras->first()->distanciaMetros)->toBeLessThan(1130.0);
});

test('calcula a distância de um elemento do tipo way a partir do seu centro geométrico', function () {
    $finder = finderComElementos([
        ['tags' => ['name' => 'Quadra em Way'], 'center' => ['lat' => -8.0576, 'lon' => -34.8770]],
    ]);

    $quadras = $finder->buscar(-8.0476, -34.8770, 5000);

    expect($quadras)->toHaveCount(1)
        ->and($quadras->first()->distanciaMetros)->toBeGreaterThan(1100.0)
        ->and($quadras->first()->distanciaMetros)->toBeLessThan(1130.0);
});

test('descarta elementos sem a tag name', function () {
    $finder = finderComElementos([
        ['tags' => ['name' => 'Quadra com Nome', 'sport' => 'soccer'], 'lat' => -8.05, 'lon' => -34.88],
        ['tags' => ['sport' => 'soccer'], 'lat' => -8.06, 'lon' => -34.89],
    ]);

    $quadras = $finder->buscar(-8.0476, -34.8770, 5000);

    expect($quadras)->toHaveCount(1)
        ->and($quadras->first()->nome)->toBe('Quadra com Nome');
});

test('traduz a tag sport do OpenStreetMap para português, com fallback para tags desconhecidas ou ausentes', function () {
    $finder = finderComElementos([
        ['tags' => ['name' => 'Quadra de Vôlei', 'sport' => 'volleyball'], 'lat' => -8.05, 'lon' => -34.88],
        ['tags' => ['name' => 'Quadra Sem Esporte Definido'], 'lat' => -8.05, 'lon' => -34.88],
        ['tags' => ['name' => 'Quadra com Esporte Raro', 'sport' => 'skateboard'], 'lat' => -8.05, 'lon' => -34.88],
    ]);

    $quadras = $finder->buscar(-8.0476, -34.8770, 5000)->keyBy('nome');

    expect($quadras['Quadra de Vôlei']->esporte)->toBe('Vôlei')
        ->and($quadras['Quadra Sem Esporte Definido']->esporte)->toBe('Esportiva')
        ->and($quadras['Quadra com Esporte Raro']->esporte)->toBe('Esportiva');
});
