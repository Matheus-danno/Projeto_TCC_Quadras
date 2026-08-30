<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ParticipacaoSala extends Pivot
{
    protected $table = 'participacao_salas';

    public $incrementing = true;

    protected $fillable = [
        'sala_id',
        'user_id',
        'forma_pagamento',
        'valor_pago',
    ];
}
