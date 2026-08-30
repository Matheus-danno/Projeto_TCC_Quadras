<?php

namespace Database\Factories;

use App\Enums\Esporte;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Quadra>
 */
class QuadraFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'dono_id' => User::factory(),
            'nome' => fake()->company().' Arena',
            'endereco' => fake()->streetAddress(),
            'cidade' => fake()->city(),
            'bairro' => fake()->citySuffix(),
            'latitude' => fake()->latitude(-23.7, -22.7),
            'longitude' => fake()->longitude(-47.0, -46.0),
            'esporte' => fake()->randomElement(Esporte::cases())->value,
            'valor_hora' => fake()->randomFloat(2, 40, 200),
            'cobertura' => fake()->boolean(),
            'descricao' => fake()->sentence(),
        ];
    }
}
