<?php

namespace App\Enums;

enum StatusAprovacaoQuadra: string
{
    case Pendente = 'pendente';
    case Aprovada = 'aprovada';
    case Suspensa = 'suspensa';

    public function label(): string
    {
        return match ($this) {
            self::Pendente => 'Pendente de aprovação',
            self::Aprovada => 'Aprovada',
            self::Suspensa => 'Suspensa',
        };
    }

    /**
     * Cor usada em badges (flux:badge) para representar este status.
     */
    public function cor(): string
    {
        return match ($this) {
            self::Pendente => 'orange',
            self::Aprovada => 'green',
            self::Suspensa => 'red',
        };
    }
}
