<?php

namespace App\Enums;

enum NivelHabilidade: string
{
    case Iniciante = 'iniciante';
    case Intermediario = 'intermediario';
    case Avancado = 'avancado';

    public function label(): string
    {
        return match ($this) {
            self::Iniciante => 'Iniciante',
            self::Intermediario => 'Intermediário',
            self::Avancado => 'Avançado',
        };
    }

    /**
     * Posição do nível numa escala crescente, usada para calcular compatibilidade.
     */
    public function ordem(): int
    {
        return match ($this) {
            self::Iniciante => 0,
            self::Intermediario => 1,
            self::Avancado => 2,
        };
    }

    /**
     * Um jogador é compatível com uma sala do mesmo nível ou de um nível adjacente.
     */
    public function compativelCom(self $outro): bool
    {
        return abs($this->ordem() - $outro->ordem()) <= 1;
    }
}
