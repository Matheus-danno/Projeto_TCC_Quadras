<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class QuadraFoto extends Model
{
    protected $fillable = [
        'quadra_id',
        'caminho',
        'capa',
        'ordem',
    ];

    protected function casts(): array
    {
        return [
            'capa' => 'boolean',
            'ordem' => 'integer',
        ];
    }

    public function quadra(): BelongsTo
    {
        return $this->belongsTo(Quadra::class);
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->caminho);
    }
}
