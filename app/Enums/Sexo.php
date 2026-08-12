<?php

namespace App\Enums;

enum Sexo: string
{
    case Masculino = 'masculino';
    case Feminino = 'feminino';

    public function label(): string
    {
        return match ($this) {
            self::Masculino => 'Masculino',
            self::Feminino => 'Feminino',
        };
    }
}
