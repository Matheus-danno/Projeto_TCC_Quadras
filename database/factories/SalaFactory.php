<?php

namespace Database\Factories;

use App\Enums\Esporte;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sala>
 */
class SalaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quadra_id' => null,
            'criador_id' => User::factory(),
            'nome' => 'Racha de '.fake()->dayOfWeek(),
            'esporte' => fake()->randomElement(Esporte::cases())->value,
            'max_participantes' => fake()->numberBetween(6, 22),
        ];
    }
}
