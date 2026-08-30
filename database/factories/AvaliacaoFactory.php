<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Avaliacao>
 */
class AvaliacaoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'avaliado_id' => User::factory(),
            'autor_id' => User::factory(),
            'nota' => fake()->numberBetween(1, 5),
            'comentario' => fake()->optional()->sentence(),
        ];
    }
}
