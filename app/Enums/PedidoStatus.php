<?php

namespace App\Enums;

enum PedidoStatus: string
{
    case Aguardando = 'aguardando';
    case Retirado = 'retirado';
    case Cancelado = 'cancelado';

    public function label(): string
    {
        return match ($this) {
            self::Aguardando => 'Aguardando Retirada',
            self::Retirado => 'Retirado',
            self::Cancelado => 'Cancelado',
        };
    }

    /**
     * Cor do badge (paleta Flux) para exibir o status no painel do dono.
     */
    public function corBadge(): string
    {
        return match ($this) {
            self::Aguardando => 'amber',
            self::Retirado => 'green',
            self::Cancelado => 'red',
        };
    }
}
