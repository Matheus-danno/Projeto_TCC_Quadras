<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MensagemSala extends Model
{
    /** @use HasFactory<\Database\Factories\MensagemSalaFactory> */
    use HasFactory;

    protected $fillable = [
        'sala_id',
        'user_id',
        'texto',
    ];

    public function sala(): BelongsTo
    {
        return $this->belongsTo(Sala::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Tempo decorrido desde o envio, formatado de forma compacta em português
     * (ex.: "26 segundos", "5 minutos", "3 horas", "2 dias").
     */
    public function tempoDecorrido(): string
    {
        $segundos = (int) $this->created_at->diffInSeconds(now());

        return match (true) {
            $segundos < 60 => $segundos.' segundo'.($segundos === 1 ? '' : 's'),
            $segundos < 3600 => ($minutos = intdiv($segundos, 60)).' minuto'.($minutos === 1 ? '' : 's'),
            $segundos < 86400 => ($horas = intdiv($segundos, 3600)).' hora'.($horas === 1 ? '' : 's'),
            default => ($dias = intdiv($segundos, 86400)).' dia'.($dias === 1 ? '' : 's'),
        };
    }
}
