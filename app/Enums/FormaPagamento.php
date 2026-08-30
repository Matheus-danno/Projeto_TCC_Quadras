<?php

namespace App\Enums;

enum FormaPagamento: string
{
    case Cartao = 'cartao';
    case Pix = 'pix';

    public function label(): string
    {
        return match ($this) {
            self::Cartao => 'Cartão de Crédito',
            self::Pix => 'Pix',
        };
    }
}
