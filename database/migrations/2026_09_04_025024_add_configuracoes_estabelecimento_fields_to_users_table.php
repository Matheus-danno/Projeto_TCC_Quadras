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
            $table->json('horario_funcionamento')->nullable()->after('estado');
            $table->json('metodos_pagamento_aceitos')->nullable()->after('horario_funcionamento');

            $table->boolean('notif_dono_dias_uteis')->default(true)->after('notif_ofertas_novidades');
            $table->boolean('notif_dono_cancelamento')->default(true)->after('notif_dono_dias_uteis');
            $table->boolean('notif_dono_mensagens_clientes')->default(true)->after('notif_dono_cancelamento');

            $table->boolean('pausa_ativa')->default(false)->after('notif_dono_mensagens_clientes');
            $table->string('pausa_motivo')->nullable()->after('pausa_ativa');
            $table->date('pausa_ate')->nullable()->after('pausa_motivo');
            $table->boolean('pausa_indeterminada')->default(false)->after('pausa_ate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'horario_funcionamento',
                'metodos_pagamento_aceitos',
                'notif_dono_dias_uteis',
                'notif_dono_cancelamento',
                'notif_dono_mensagens_clientes',
                'pausa_ativa',
                'pausa_motivo',
                'pausa_ate',
                'pausa_indeterminada',
            ]);
        });
    }
};
