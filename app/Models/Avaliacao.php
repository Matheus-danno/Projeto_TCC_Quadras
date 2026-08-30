<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Avaliacao extends Model
{
    /** @use HasFactory<\Database\Factories\AvaliacaoFactory> */
    use HasFactory;

    protected $table = 'avaliacoes';

    protected $fillable = [
        'avaliado_id',
        'autor_id',
        'nota',
        'comentario',
    ];

    protected function casts(): array
    {
        return [
            'nota' => 'integer',
        ];
    }

    public function avaliado(): BelongsTo
    {
        return $this->belongsTo(User::class, 'avaliado_id');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autor_id');
    }
}
