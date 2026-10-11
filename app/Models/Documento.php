<?php

namespace App\Models;

use App\Enums\TipoDocumento;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Documento extends Model
{
    protected $fillable = [
        'tipo',
        'caminho',
        'nome_original',
        'tamanho_bytes',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => TipoDocumento::class,
            'tamanho_bytes' => 'integer',
        ];
    }

    public function documentavel(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Link temporário e assinado para visualizar o documento — nunca uma
     * URL pública permanente, já que fica no disco privado "local".
     */
    public function url(int $minutos = 15): string
    {
        return Storage::disk('local')->temporaryUrl($this->caminho, now()->addMinutes($minutos));
    }

    public function tamanhoFormatado(): string
    {
        return number_format($this->tamanho_bytes / 1024 / 1024, 1, ',', '.').' MB';
    }
}
