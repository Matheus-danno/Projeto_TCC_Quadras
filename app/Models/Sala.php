<?php

namespace App\Models;

use App\Enums\Esporte;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Sala extends Model
{
    /** @use HasFactory<\Database\Factories\SalaFactory> */
    use HasFactory;

    protected $fillable = [
        'quadra_id',
        'criador_id',
        'nome',
        'esporte',
        'max_participantes',
    ];

    protected function casts(): array
    {
        return [
            'esporte' => Esporte::class,
        ];
    }

    public function quadra(): BelongsTo
    {
        return $this->belongsTo(Quadra::class);
    }

    public function criador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'criador_id');
    }

    public function participantes(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'participacao_salas')
            ->using(ParticipacaoSala::class)
            ->withTimestamps();
    }
}
