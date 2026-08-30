<?php

namespace App\Enums;

enum Privacidade: string
{
    case Publica = 'publica';
    case Privada = 'privada';

    public function label(): string
    {
        return match ($this) {
            self::Publica => 'Pública',
            self::Privada => 'Privada',
        };
    }

    public function descricao(): string
    {
        return match ($this) {
            self::Publica => 'Qualquer jogador compatível pode entrar',
            self::Privada => 'Só entra quem receber o link da sala',
        };
    }
}
