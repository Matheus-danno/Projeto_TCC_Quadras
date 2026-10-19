<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Lançada ao agendar uma reserva quando o horário já está ocupado na quadra
 * no momento exato da gravação — seja pela verificação feita com a quadra
 * travada, seja pela exclusion constraint do PostgreSQL (reserva concorrente).
 */
class HorarioIndisponivelException extends RuntimeException
{
    public function __construct(?Throwable $previous = null)
    {
        parent::__construct('Esse horário já está reservado para esta quadra.', 0, $previous);
    }
}
