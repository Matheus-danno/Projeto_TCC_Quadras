<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Preferências de notificação de jogador e de dono de quadra numa
     * tabela só, separada de "users" (que guarda ambos os papéis,
     * diferenciados pela coluna "role").
     */
    public function up(): void
    {
        Schema::create('noticacao_usuarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            // Jogador
            $table->boolean('notif_confirmacao_reserva')->default(true);
            $table->boolean('notif_lembrete_horario')->default(true);
            $table->boolean('notif_novo_jogador_sala')->default(true);
            $table->boolean('notif_mensagens_grupo')->default(true);
            $table->boolean('notif_ofertas_novidades')->default(true);

            // Dono de quadra
            $table->boolean('notif_dono_dias_uteis')->default(true);
            $table->boolean('notif_dono_cancelamento')->default(true);
            $table->boolean('notif_dono_mensagens_clientes')->default(true);

            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'notif_confirmacao_reserva',
                'notif_lembrete_horario',
                'notif_novo_jogador_sala',
                'notif_mensagens_grupo',
                'notif_ofertas_novidades',
                'notif_dono_dias_uteis',
                'notif_dono_cancelamento',
                'notif_dono_mensagens_clientes',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('notif_confirmacao_reserva')->default(true);
            $table->boolean('notif_lembrete_horario')->default(true);
            $table->boolean('notif_novo_jogador_sala')->default(true);
            $table->boolean('notif_mensagens_grupo')->default(true);
            $table->boolean('notif_ofertas_novidades')->default(true);
            $table->boolean('notif_dono_dias_uteis')->default(true);
            $table->boolean('notif_dono_cancelamento')->default(true);
            $table->boolean('notif_dono_mensagens_clientes')->default(true);
        });

        Schema::dropIfExists('noticacao_usuarios');
    }
};
