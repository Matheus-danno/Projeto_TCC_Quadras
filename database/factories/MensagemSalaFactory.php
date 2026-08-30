<?php

namespace Database\Factories;

use App\Models\Sala;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MensagemSala>
 */
class MensagemSalaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sala_id' => Sala::factory(),
            'user_id' => User::factory(),
            'texto' => fake()->sentence(),
        ];
    }
}
