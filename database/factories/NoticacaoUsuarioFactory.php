<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NoticacaoUsuario>
 */
class NoticacaoUsuarioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'notif_confirmacao_reserva' => true,
            'notif_lembrete_horario' => true,
            'notif_novo_jogador_sala' => true,
            'notif_mensagens_grupo' => true,
            'notif_ofertas_novidades' => true,
            'notif_dono_dias_uteis' => true,
            'notif_dono_cancelamento' => true,
            'notif_dono_mensagens_clientes' => true,
        ];
    }
}
