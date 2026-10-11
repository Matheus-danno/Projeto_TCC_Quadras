<?php

namespace App\Enums;

/**
 * Status do fluxo de aprovação de cadastros (proprietários e quadras).
 * "Suspenso" não faz parte do fluxo de análise em si — é a ação de
 * moderação que o admin pode aplicar depois que o cadastro já está
 * aprovado (ex.: suspender uma quadra que violou alguma regra).
 */
enum StatusCadastro: string
{
    case Pendente = 'pendente';
    case EmAnalise = 'em_analise';
    case AjustesSolicitados = 'ajustes_solicitados';
    case Aprovado = 'aprovado';
    case Rejeitado = 'rejeitado';
    case Suspenso = 'suspenso';

    public function label(): string
    {
        return match ($this) {
            self::Pendente => 'Pendente',
            self::EmAnalise => 'Em análise',
            self::AjustesSolicitados => 'Ajustes solicitados',
            self::Aprovado => 'Aprovado',
            self::Rejeitado => 'Rejeitado',
            self::Suspenso => 'Suspenso',
        };
    }

    /**
     * Cor usada em badges (flux:badge) para representar este status.
     */
    public function cor(): string
    {
        return match ($this) {
            self::Pendente => 'orange',
            self::EmAnalise => 'blue',
            self::AjustesSolicitados => 'amber',
            self::Aprovado => 'green',
            self::Rejeitado => 'red',
            self::Suspenso => 'zinc',
        };
    }

    /**
     * Se o cadastro ainda está aguardando alguma ação do admin (conta
     * para o contador de pendências da fila de aprovações).
     */
    public function pendenteDeAnalise(): bool
    {
        return in_array($this, [self::Pendente, self::EmAnalise], true);
    }

    /**
     * Se o dono pode editar e reenviar o cadastro neste status.
     */
    public function podeReenviar(): bool
    {
        return in_array($this, [self::AjustesSolicitados, self::Rejeitado], true);
    }
}
