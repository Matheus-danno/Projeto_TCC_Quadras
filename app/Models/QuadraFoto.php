<?php

namespace App\Models;

use App\Enums\StatusModeracaoFoto;
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
        'status_moderacao',
        'motivo_recusa',
    ];

    protected function casts(): array
    {
        return [
            'capa' => 'boolean',
            'ordem' => 'integer',
            'status_moderacao' => StatusModeracaoFoto::class,
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
