<?php

namespace App\Enums;

enum TipoPessoa: string
{
    case Fisica = 'fisica';
    case Juridica = 'juridica';

    public function label(): string
    {
        return match ($this) {
            self::Fisica => 'Pessoa física (CPF)',
            self::Juridica => 'Pessoa jurídica (CNPJ)',
        };
    }
}
