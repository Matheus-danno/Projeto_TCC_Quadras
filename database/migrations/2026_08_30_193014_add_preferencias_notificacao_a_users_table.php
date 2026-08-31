<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('notif_confirmacao_reserva')->default(true);
            $table->boolean('notif_lembrete_horario')->default(true);
            $table->boolean('notif_novo_jogador_sala')->default(true);
            $table->boolean('notif_mensagens_grupo')->default(true);
            $table->boolean('notif_ofertas_novidades')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'notif_confirmacao_reserva',
                'notif_lembrete_horario',
                'notif_novo_jogador_sala',
                'notif_mensagens_grupo',
                'notif_ofertas_novidades',
            ]);
        });
    }
};
