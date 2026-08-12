<?php

namespace App\Enums;

enum Esporte: string
{
    case Futebol = 'futebol';
    case Futsal = 'futsal';
    case Volei = 'volei';
    case Basquete = 'basquete';
    case Tenis = 'tenis';
    case BeachTennis = 'beach_tennis';

    public function label(): string
    {
        return match ($this) {
            self::Futebol => 'Futebol',
            self::Futsal => 'Futsal',
            self::Volei => 'Vôlei',
            self::Basquete => 'Basquete',
            self::Tenis => 'Tênis',
            self::BeachTennis => 'Beach Tennis',
        };
    }
}
