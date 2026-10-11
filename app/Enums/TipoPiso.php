<?php

namespace App\Enums;

enum TipoPiso: string
{
    case Sintetico = 'sintetico';
    case Natural = 'natural';
    case Emborrachado = 'emborrachado';
    case Taco = 'taco';
    case Cimento = 'cimento';
    case Areia = 'areia';
    case Outro = 'outro';

    public function label(): string
    {
        return match ($this) {
            self::Sintetico => 'Grama sintética',
            self::Natural => 'Grama natural',
            self::Emborrachado => 'Piso emborrachado',
            self::Taco => 'Taco/parquet',
            self::Cimento => 'Cimento/concreto',
            self::Areia => 'Areia',
            self::Outro => 'Outro',
        };
    }
}
