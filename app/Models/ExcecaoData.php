<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExcecaoData extends Model
{
    protected $table = 'excecoes_data';

    protected $fillable = [
        'dono_id',
        'data',
        'descricao',
        'fechado_dia_todo',
        'hora_abertura',
        'hora_fechamento',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'date',
            'fechado_dia_todo' => 'boolean',
        ];
    }

    public function dono(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dono_id');
    }
}
