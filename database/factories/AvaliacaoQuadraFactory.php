<?php

namespace Database\Factories;

use App\Models\Quadra;
use App\Models\Sala;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AvaliacaoQuadra>
 */
class AvaliacaoQuadraFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quadra_id' => Quadra::factory(),
            'sala_id' => Sala::factory(),
            'autor_id' => User::factory(),
            'nota' => fake()->numberBetween(1, 5),
            'comentario' => fake()->optional()->sentence(),
        ];
    }
}
