<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Repasse extends Model
{
    protected $fillable = [
        'dono_id',
        'valor_centavos',
        'observacao',
        'criado_por_id',
    ];

    protected function casts(): array
    {
        return [
            'valor_centavos' => 'integer',
        ];
    }

    public function dono(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dono_id');
    }

    public function criadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'criado_por_id');
    }
}
