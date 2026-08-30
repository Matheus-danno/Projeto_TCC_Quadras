<?php

namespace App\Enums;

enum Aprovacao: string
{
    case Manual = 'manual';
    case Automatica = 'automatica';

    public function label(): string
    {
        return match ($this) {
            self::Manual => 'Aprovar manualmente',
            self::Automatica => 'Entrada automática',
        };
    }

    public function descricao(): string
    {
        return match ($this) {
            self::Manual => 'Você decide quem entra na sala',
            self::Automatica => 'Confirmação instantânea após pagamento',
        };
    }
}
