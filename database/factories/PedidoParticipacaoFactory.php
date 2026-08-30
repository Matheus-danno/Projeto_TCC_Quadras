<?php

namespace Database\Factories;

use App\Enums\PedidoParticipacaoStatus;
use App\Models\Sala;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PedidoParticipacao>
 */
class PedidoParticipacaoFactory extends Factory
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
            'status' => PedidoParticipacaoStatus::Pendente,
        ];
    }
}
