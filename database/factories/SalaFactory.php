<?php

namespace Database\Factories;

use App\Enums\AceitacaoNivel;
use App\Enums\Esporte;
use App\Enums\NivelHabilidade;
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
            'total_jogadores' => null,
            'data' => null,
            'horario_inicio' => null,
            'horario_fim' => null,
            'destaque' => false,
            'preco_pessoa' => null,
            'nivel_desejado' => fake()->randomElement(NivelHabilidade::cases())->value,
            'aceitacao_niveis_adjacentes' => AceitacaoNivel::Nenhum->value,
            'privacidade' => 'publica',
            'aprovacao' => 'automatica',
            'status' => 'aberta',
            'regras_adicionais' => null,
            'reserva_id' => null,
        ];
    }
}
