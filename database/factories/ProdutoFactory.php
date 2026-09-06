<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Produto>
 */
class ProdutoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'dono_id' => User::factory()->donoQuadra(),
            'nome' => fake()->words(3, true),
            'descricao' => fake()->sentence(),
            'categoria' => fake()->randomElement(['Vestuário', 'Calçados', 'Acessórios', 'Hidratação']),
            'preco' => fake()->randomFloat(2, 20, 500),
            'estoque' => fake()->numberBetween(0, 50),
            'ativo' => true,
            'imagem' => null,
        ];
    }
}
