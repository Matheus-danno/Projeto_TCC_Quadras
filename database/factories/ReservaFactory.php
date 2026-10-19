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
    private static int $sequencia = 0;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Dia (0-30) e hora (7h-21h) avançam juntos a cada reserva criada, em
        // vez de sorteados: reservas feitas em sequência nunca repetem dia nem
        // hora, mesmo quando o teste fixa um dos dois. Assim não se sobrepõem
        // por acaso — o banco rejeita sobreposição (reservas_sem_sobreposicao).
        $sequencia = self::$sequencia++;
        $horaInicio = 7 + $sequencia % 15;

        return [
            'quadra_id' => Quadra::factory(),
            'user_id' => User::factory(),
            'data' => now()->addDays($sequencia % 31)->format('Y-m-d'),
            'hora_inicio' => sprintf('%02d:00:00', $horaInicio),
            'hora_fim' => sprintf('%02d:00:00', $horaInicio + 1),
            'status' => fake()->randomElement(ReservaStatus::cases())->value,
        ];
    }
}
