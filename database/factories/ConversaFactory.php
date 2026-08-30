<?php

namespace Database\Factories;

use App\Models\Quadra;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Conversa>
 */
class ConversaFactory extends Factory
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
            'jogador_id' => User::factory(),
        ];
    }
}
