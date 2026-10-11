<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Valida um CPF pelo algoritmo oficial de dígitos verificadores (módulo 11),
 * não apenas a quantidade de dígitos.
 */
class CpfValido implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $cpf = preg_replace('/\D/', '', (string) $value);

        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf) === 1) {
            $fail('Informe um CPF válido.');

            return;
        }

        if (! $this->digitosVerificadoresValidos($cpf)) {
            $fail('Informe um CPF válido.');
        }
    }

    private function digitosVerificadoresValidos(string $cpf): bool
    {
        $calcularDigito = function (string $base) {
            $soma = 0;
            $pesoInicial = strlen($base) + 1;

            foreach (str_split($base) as $indice => $digito) {
                $soma += ((int) $digito) * ($pesoInicial - $indice);
            }

            $resto = $soma % 11;

            return $resto < 2 ? 0 : 11 - $resto;
        };

        $primeiroDigito = $calcularDigito(substr($cpf, 0, 9));

        if ($primeiroDigito !== (int) $cpf[9]) {
            return false;
        }

        $segundoDigito = $calcularDigito(substr($cpf, 0, 9).$primeiroDigito);

        return $segundoDigito === (int) $cpf[10];
    }
}
