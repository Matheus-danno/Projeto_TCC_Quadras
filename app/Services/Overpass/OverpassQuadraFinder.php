<?php

namespace App\Services\Overpass;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Collection;

class OverpassQuadraFinder
{
    private const CACHE_TTL_SEGUNDOS = 900;

    /**
     * Mantenha as chaves em sincronia com OverpassClient::SPORTS_SUPORTADOS —
     * é essa lista que decide quais tags "sport" a consulta traz da Overpass API.
     */
    private const TRADUCAO_ESPORTES = [
        'soccer' => 'Futebol',
        'futsal' => 'Futsal',
        'tennis' => 'Tênis',
        'volleyball' => 'Vôlei',
        'beachvolleyball' => 'Vôlei de Praia',
        'multi' => 'Poliesportiva',
    ];

    public function __construct(
        private readonly OverpassClient $client,
        private readonly CacheRepository $cache,
    ) {}

    /**
     * @return Collection<int, QuadraProxima>
     */
    public function buscar(float $lat, float $lon, int $raioMetros): Collection
    {
        return $this->cache->remember(
            $this->chaveCache($lat, $lon, $raioMetros),
            self::CACHE_TTL_SEGUNDOS,
            fn () => $this->buscarSemCache($lat, $lon, $raioMetros),
        );
    }

    /**
     * @return Collection<int, QuadraProxima>
     */
    private function buscarSemCache(float $lat, float $lon, int $raioMetros): Collection
    {
        return collect($this->client->buscar($lat, $lon, $raioMetros))
            ->filter(fn (array $elemento) => filled($elemento['tags']['name'] ?? null))
            ->map(function (array $elemento) use ($lat, $lon) {
                [$latElemento, $lonElemento] = $this->coordenadas($elemento);

                return new QuadraProxima(
                    nome: $elemento['tags']['name'],
                    esporte: $this->traduzirEsporte($elemento['tags']['sport'] ?? null),
                    latitude: $latElemento,
                    longitude: $lonElemento,
                    distanciaMetros: $this->calcularDistanciaMetros($lat, $lon, $latElemento, $lonElemento),
                );
            })
            ->sortBy(fn (QuadraProxima $quadra) => $quadra->distanciaMetros)
            ->values();
    }

    /**
     * @param  array<string, mixed>  $elemento
     * @return array{0: float, 1: float}
     */
    private function coordenadas(array $elemento): array
    {
        if (isset($elemento['lat'], $elemento['lon'])) {
            return [(float) $elemento['lat'], (float) $elemento['lon']];
        }

        return [(float) $elemento['center']['lat'], (float) $elemento['center']['lon']];
    }

    private function traduzirEsporte(?string $tagSport): string
    {
        return self::TRADUCAO_ESPORTES[$tagSport] ?? 'Esportiva';
    }

    private function calcularDistanciaMetros(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $raioTerraMetros = 6371000;

        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLon = deg2rad($lon2 - $lon1);

        $a = sin($deltaLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($deltaLon / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $raioTerraMetros * $c;
    }

    private function chaveCache(float $lat, float $lon, int $raioMetros): string
    {
        return sprintf('overpass:quadras:%.3f:%.3f:%d', $lat, $lon, $raioMetros);
    }
}
