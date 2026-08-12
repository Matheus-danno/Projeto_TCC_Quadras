<?php

namespace App\Enums;

enum UserRole: string
{
    case Jogador = 'jogador';
    case DonoQuadra = 'dono_quadra';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Jogador => 'Jogador',
            self::DonoQuadra => 'Dono de Quadra',
            self::Admin => 'Administrador',
        };
    }
}
