<?php

namespace App\Enums;

enum AceitacaoNivel: string
{
    case Nenhum = 'nenhum';
    case Abaixo = 'abaixo';
    case Acima = 'acima';
    case Todos = 'todos';

    public function label(): string
    {
        return match ($this) {
            self::Nenhum => 'Não aceitar',
            self::Abaixo => 'Iniciante',
            self::Acima => 'Avançado',
            self::Todos => 'Aceitar todos',
        };
    }

    /**
     * Diferença máxima de "ordem" de nível aceita a partir do nível da sala.
     * Ex.: Todos aceita 1 nível acima ou abaixo; Nenhum exige o nível exato.
     */
    public function aceitaAbaixo(): bool
    {
        return in_array($this, [self::Abaixo, self::Todos], true);
    }

    public function aceitaAcima(): bool
    {
        return in_array($this, [self::Acima, self::Todos], true);
    }
}
