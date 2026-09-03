<?php

namespace App\Services\Geocoding;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class NominatimClient
{
    private const HTTP_TIMEOUT_SEGUNDOS = 10;

    /**
     * Geocodifica um endereço em coordenadas via Nominatim (API de busca do
     * OpenStreetMap, mesma família da Overpass API já usada no projeto).
     *
     * Retorna null quando o endereço não é encontrado ou o serviço está
     * indisponível — a geocodificação é automática e opcional no cadastro de
     * quadra, uma falha aqui não deve impedir o dono de salvar o cadastro.
     *
     * @return array{0: float, 1: float}|null
     */
    public function geocodificar(string $endereco): ?array
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => config('services.nominatim.user_agent'),
            ])
                ->timeout(self::HTTP_TIMEOUT_SEGUNDOS)
                ->get(config('services.nominatim.url'), [
                    'q' => $endereco,
                    'format' => 'json',
                    'limit' => 1,
                    'countrycodes' => 'br',
                ]);
        } catch (ConnectionException) {
            return null;
        }

        if ($response->failed()) {
            return null;
        }

        $resultado = $response->json(0);

        if (! isset($resultado['lat'], $resultado['lon'])) {
            return null;
        }

        return [(float) $resultado['lat'], (float) $resultado['lon']];
    }
}
