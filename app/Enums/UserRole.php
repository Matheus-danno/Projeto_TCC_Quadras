<?php

namespace App\Enums;

enum UserRole: string
{
    case Jogador = 'jogador';
    case DonoQuadra = 'dono_quadra';

    public function label(): string
    {
        return match ($this) {
            self::Jogador => 'Jogador',
            self::DonoQuadra => 'Dono de Quadra',
        };
    }

    /**
     * Rota para onde o usuário deve ser levado após autenticar.
     */
    public function dashboardRoute(): string
    {
        return match ($this) {
            self::Jogador => 'quadras.index',
            self::DonoQuadra => 'painel.dashboard',
        };
    }
}
