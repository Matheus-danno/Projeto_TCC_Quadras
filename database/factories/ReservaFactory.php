<?php

namespace Database\Factories;

use App\Enums\ReservaStatus;
use App\Models\Quadra;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reserva>
 */
class ReservaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $horaInicio = fake()->numberBetween(7, 21);

        return [
            'quadra_id' => Quadra::factory(),
            'user_id' => User::factory(),
            'data' => fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'hora_inicio' => sprintf('%02d:00:00', $horaInicio),
            'hora_fim' => sprintf('%02d:00:00', $horaInicio + 1),
            'status' => fake()->randomElement(ReservaStatus::cases())->value,
        ];
    }
}
