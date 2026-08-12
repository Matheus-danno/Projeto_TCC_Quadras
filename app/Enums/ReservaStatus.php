<?php

namespace App\Enums;

enum ReservaStatus: string
{
    case Pendente = 'pendente';
    case Confirmada = 'confirmada';
    case Cancelada = 'cancelada';

    public function label(): string
    {
        return match ($this) {
            self::Pendente => 'Pendente',
            self::Confirmada => 'Confirmada',
            self::Cancelada => 'Cancelada',
        };
    }
}
