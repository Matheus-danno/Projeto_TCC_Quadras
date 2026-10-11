<?php

namespace Database\Factories;

use App\Enums\Esporte;
use App\Enums\StatusCadastro;
use App\Enums\TipoPiso;
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
            'cep' => fake()->postcode(),
            'latitude' => fake()->latitude(-23.7, -22.7),
            'longitude' => fake()->longitude(-47.0, -46.0),
            'esporte' => fake()->randomElement(Esporte::cases())->value,
            'valor_hora' => fake()->randomFloat(2, 40, 200),
            'capacidade_maxima' => fake()->numberBetween(4, 60),
            'cobertura' => fake()->boolean(),
            'tipo_piso' => fake()->randomElement(TipoPiso::cases())->value,
            'descricao' => fake()->sentence(),
            'status_cadastro' => StatusCadastro::Aprovado,
        ];
    }

    /**
     * Indica que a quadra ainda não foi aprovada (estado real de uma quadra
     * recém-cadastrada, antes da fábrica assumir o padrão "aprovado" por
     * conveniência dos outros testes).
     */
    public function pendente(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_cadastro' => StatusCadastro::Pendente,
        ]);
    }
}
