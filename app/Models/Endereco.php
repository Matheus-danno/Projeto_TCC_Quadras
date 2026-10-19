<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Endereco extends Model
{
    /** @use HasFactory<\Database\Factories\EnderecoFactory> */
    use HasFactory;

    protected $fillable = [
        'cep',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Logradouro e número numa única linha, no formato usado nas telas
     * (ex.: "Rua das Flores, 100").
     */
    public function linha(): string
    {
        return $this->numero
            ? "{$this->logradouro}, {$this->numero}"
            : (string) $this->logradouro;
    }
}
