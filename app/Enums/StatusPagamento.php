<?php

namespace App\Enums;

enum StatusPagamento: string
{
    case Pago = 'pago';
    case Pendente = 'pendente';
    case Isento = 'isento';

    public function label(): string
    {
        return match ($this) {
            self::Pago => 'Pago',
            self::Pendente => 'Pendente',
            self::Isento => 'Isento',
        };
    }
}
