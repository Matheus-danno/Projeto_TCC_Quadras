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

    /**
     * Rota para onde o usuário deve ser levado após autenticar.
     */
    public function dashboardRoute(): string
    {
        return match ($this) {
            self::Jogador => 'quadras.index',
            self::DonoQuadra => 'painel.dashboard',
            self::Admin => 'admin.dashboard',
        };
    }
}
