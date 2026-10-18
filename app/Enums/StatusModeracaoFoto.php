<?php

namespace App\Enums;

/**
 * Status de moderação individual de cada foto de quadra, definido pelo
 * admin durante a análise do cadastro (independente do status geral da
 * quadra, que é definido pelo checklist/decisão como um todo).
 */
enum StatusModeracaoFoto: string
{
    case Pendente = 'pendente';
    case Aceita = 'aceita';
    case Recusada = 'recusada';

    public function label(): string
    {
        return match ($this) {
            self::Pendente => 'Pendente',
            self::Aceita => 'Aceita',
            self::Recusada => 'Recusada',
        };
    }
}
