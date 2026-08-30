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
        Schema::table('salas', function (Blueprint $table) {
            $table->date('data')->nullable()->after('quadra_id');
            $table->time('hora_inicio')->nullable()->after('data');
            $table->unsignedInteger('duracao_minutos')->default(90)->after('hora_inicio');
            $table->unsignedInteger('quantidade_horas')->default(1)->after('duracao_minutos');
            $table->string('nivel_desejado')->nullable()->after('max_participantes');
            $table->string('aceitacao_niveis_adjacentes')->default('nenhum')->after('nivel_desejado');
            $table->boolean('privada')->default(false)->after('aceitacao_niveis_adjacentes');
            $table->boolean('aprovacao_manual')->default(false)->after('privada');
            $table->text('regras_adicionais')->nullable()->after('aprovacao_manual');
            $table->foreignId('reserva_id')->nullable()->after('regras_adicionais')->constrained('reservas')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reserva_id');
            $table->dropColumn([
                'data',
                'hora_inicio',
                'duracao_minutos',
                'quantidade_horas',
                'nivel_desejado',
                'aceitacao_niveis_adjacentes',
                'privada',
                'aprovacao_manual',
                'regras_adicionais',
            ]);
        });
    }
};
