<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Valida um CNPJ pelo algoritmo oficial de dígitos verificadores (módulo 11),
 * não apenas a quantidade de dígitos.
 */
class CnpjValido implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $cnpj = preg_replace('/\D/', '', (string) $value);

        if (strlen($cnpj) !== 14 || preg_match('/^(\d)\1{13}$/', $cnpj) === 1) {
            $fail('Informe um CNPJ válido.');

            return;
        }

        if (! $this->digitosVerificadoresValidos($cnpj)) {
            $fail('Informe um CNPJ válido.');
        }
    }

    private function digitosVerificadoresValidos(string $cnpj): bool
    {
        $calcularDigito = function (string $base, array $pesos): int {
            $soma = 0;

            foreach ($pesos as $indice => $peso) {
                $soma += ((int) $base[$indice]) * $peso;
            }

            $resto = $soma % 11;

            return $resto < 2 ? 0 : 11 - $resto;
        };

        $primeiroDigito = $calcularDigito(substr($cnpj, 0, 12), [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]);

        if ($primeiroDigito !== (int) $cnpj[12]) {
            return false;
        }

        $segundoDigito = $calcularDigito(substr($cnpj, 0, 12).$primeiroDigito, [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]);

        return $segundoDigito === (int) $cnpj[13];
    }
}
