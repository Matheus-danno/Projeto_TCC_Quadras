<?php

namespace App\Services\Overpass;

final readonly class QuadraProxima
{
    public function __construct(
        public string $nome,
        public string $esporte,
        public float $latitude,
        public float $longitude,
        public float $distanciaMetros,
    ) {}

    public function distanciaFormatada(): string
    {
        if ($this->distanciaMetros < 1000) {
            return round($this->distanciaMetros).' m';
        }

        return number_format($this->distanciaMetros / 1000, 1, ',', '.').' km';
    }
}
