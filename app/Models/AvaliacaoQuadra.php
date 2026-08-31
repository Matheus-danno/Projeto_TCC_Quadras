<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvaliacaoQuadra extends Model
{
    /** @use HasFactory<\Database\Factories\AvaliacaoQuadraFactory> */
    use HasFactory;

    protected $table = 'avaliacoes_quadras';

    protected $fillable = [
        'quadra_id',
        'sala_id',
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

    public function quadra(): BelongsTo
    {
        return $this->belongsTo(Quadra::class);
    }

    public function sala(): BelongsTo
    {
        return $this->belongsTo(Sala::class);
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autor_id');
    }
}
