<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cartao>
 */
class CartaoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'nome_titular' => fake()->name(),
            'numero_final' => fake()->numerify('####'),
            'bandeira' => fake()->randomElement(['visa', 'mastercard', 'amex', 'elo']),
            'validade' => fake()->numerify('##/##'),
            'principal' => false,
        ];
    }
}
