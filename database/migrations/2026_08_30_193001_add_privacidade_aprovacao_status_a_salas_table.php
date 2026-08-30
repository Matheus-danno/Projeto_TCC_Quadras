<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('salas', function (Blueprint $table) {
            $table->time('horario_fim')->nullable()->after('hora_inicio');
            $table->boolean('destaque')->default(false)->after('quantidade_horas');
            $table->decimal('preco_pessoa', 8, 2)->nullable()->after('destaque');
            $table->unsignedInteger('total_jogadores')->nullable()->after('max_participantes');
            $table->string('privacidade')->default('publica')->after('preco_pessoa');
            $table->string('aprovacao')->default('automatica')->after('privacidade');
            $table->string('status')->default('aberta')->after('aprovacao');
        });

        DB::table('salas')->orderBy('id')->chunkById(100, function ($salas) {
            foreach ($salas as $sala) {
                if (! $sala->hora_inicio) {
                    continue;
                }

                $inicio = \Carbon\Carbon::parse($sala->hora_inicio);
                $fim = $inicio->copy()->addMinutes((int) ($sala->duracao_minutos ?: 90));

                DB::table('salas')->where('id', $sala->id)->update([
                    'horario_fim' => $fim->format('H:i:s'),
                    'privacidade' => $sala->privada ? 'privada' : 'publica',
                    'aprovacao' => $sala->aprovacao_manual ? 'manual' : 'automatica',
                ]);
            }
        });

        Schema::table('salas', function (Blueprint $table) {
            $table->renameColumn('hora_inicio', 'horario_inicio');
        });

        Schema::table('salas', function (Blueprint $table) {
            $table->dropColumn(['privada', 'aprovacao_manual', 'duracao_minutos', 'quantidade_horas']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salas', function (Blueprint $table) {
            $table->boolean('privada')->default(false);
            $table->boolean('aprovacao_manual')->default(false);
            $table->unsignedInteger('duracao_minutos')->default(90);
            $table->unsignedInteger('quantidade_horas')->default(1);
        });

        Schema::table('salas', function (Blueprint $table) {
            $table->renameColumn('horario_inicio', 'hora_inicio');
        });

        Schema::table('salas', function (Blueprint $table) {
            $table->dropColumn(['horario_fim', 'destaque', 'preco_pessoa', 'total_jogadores', 'privacidade', 'aprovacao', 'status']);
        });
    }
};
