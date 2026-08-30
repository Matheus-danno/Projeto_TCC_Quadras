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
}
