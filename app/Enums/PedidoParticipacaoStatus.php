<?php

namespace App\Enums;

enum PedidoParticipacaoStatus: string
{
    case Pendente = 'pendente';
    case Aprovado = 'aprovado';
    case Recusado = 'recusado';

    public function label(): string
    {
        return match ($this) {
            self::Pendente => 'Pendente',
            self::Aprovado => 'Aprovado',
            self::Recusado => 'Recusado',
        };
    }
}
