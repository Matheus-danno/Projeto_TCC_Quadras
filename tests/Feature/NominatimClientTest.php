<?php

use App\Services\Geocoding\NominatimClient;
use Illuminate\Support\Facades\Http;

test('geocodifica um endereço retornando latitude e longitude como float', function () {
    Http::fake([
        config('services.nominatim.url').'*' => Http::response([
            ['lat' => '-8.0476000', 'lon' => '-34.8770000'],
        ]),
    ]);

    $coordenadas = (new NominatimClient)->geocodificar('Rua Um, 100, Boa Viagem, Recife');

    expect($coordenadas)->toBe([-8.0476, -34.877]);
});

test('retorna null quando o endereço não é encontrado', function () {
    Http::fake([
        config('services.nominatim.url').'*' => Http::response([]),
    ]);

    $coordenadas = (new NominatimClient)->geocodificar('Endereço Inexistente');

    expect($coordenadas)->toBeNull();
});

test('retorna null quando a Nominatim API responde com erro', function () {
    Http::fake([
        config('services.nominatim.url').'*' => Http::response('Erro interno', 500),
    ]);

    $coordenadas = (new NominatimClient)->geocodificar('Rua Um, 100');

    expect($coordenadas)->toBeNull();
});

test('retorna null quando a conexão falha', function () {
    Http::fake([
        config('services.nominatim.url').'*' => fn () => throw new Illuminate\Http\Client\ConnectionException('timeout'),
    ]);

    $coordenadas = (new NominatimClient)->geocodificar('Rua Um, 100');

    expect($coordenadas)->toBeNull();
});
