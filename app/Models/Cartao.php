<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cartao extends Model
{
    /** @use HasFactory<\Database\Factories\CartaoFactory> */
    use HasFactory;

    protected $table = 'cartoes';

    protected $fillable = [
        'user_id',
        'nome_titular',
        'numero_final',
        'bandeira',
        'validade',
        'principal',
    ];

    protected function casts(): array
    {
        return [
            'principal' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Número do cartão mascarado para exibição, mostrando só os 4 últimos dígitos.
     */
    public function numeroMascarado(): string
    {
        return '•••• •••• •••• '.$this->numero_final;
    }

    /**
     * Ícone Bootstrap Icons representativo da bandeira do cartão.
     */
    public function bandeiraIcone(): string
    {
        return match ($this->bandeira) {
            'visa', 'mastercard', 'elo' => 'bi-credit-card-2-front-fill',
            'amex' => 'bi-credit-card-fill',
            default => 'bi-credit-card',
        };
    }

    /**
     * Identifica a bandeira do cartão a partir dos primeiros dígitos do número
     * informado, seguindo os prefixos padrão de cada bandeira.
     */
    public static function identificarBandeira(string $numeroCartao): string
    {
        $digitos = preg_replace('/\D/', '', $numeroCartao);

        return match (true) {
            str_starts_with($digitos, '4') => 'visa',
            (int) substr($digitos, 0, 2) >= 51 && (int) substr($digitos, 0, 2) <= 55 => 'mastercard',
            in_array(substr($digitos, 0, 2), ['34', '37'], true) => 'amex',
            in_array(substr($digitos, 0, 6), ['636368', '438935', '504175', '451416', '509048'], true) => 'elo',
            default => 'outro',
        };
    }
}
