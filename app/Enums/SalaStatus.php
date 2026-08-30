<?php

namespace App\Enums;

enum SalaStatus: string
{
    case Aberta = 'aberta';
    case Fechada = 'fechada';

    public function label(): string
    {
        return match ($this) {
            self::Aberta => 'Aberta',
            self::Fechada => 'Fechada',
        };
    }
}
