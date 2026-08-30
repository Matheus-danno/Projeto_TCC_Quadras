<?php

namespace App\Enums;

enum NivelHabilidade: string
{
    case Iniciante = 'iniciante';
    case Intermediario = 'intermediario';
    case Avancado = 'avancado';

    public function label(): string
    {
        return match ($this) {
            self::Iniciante => 'Iniciante',
            self::Intermediario => 'Intermediário',
            self::Avancado => 'Avançado',
        };
    }
}
