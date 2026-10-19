<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Impede no próprio banco que duas reservas ativas (não canceladas) da mesma
 * quadra ocupem faixas de horário sobrepostas, mesmo que duas requisições
 * cheguem ao mesmo tempo e ambas passem pela verificação da aplicação.
 *
 * Usa uma exclusion constraint, recurso específico do PostgreSQL: a extensão
 * btree_gist permite combinar a igualdade de quadra_id com a sobreposição (&&)
 * de intervalos de tempo no mesmo índice GiST. O intervalo é [início, fim),
 * então uma reserva que termina às 20:00 não conflita com outra que começa às
 * 20:00. Uma reserva que termina à meia-noite (hora_fim <= hora_inicio) tem o
 * fim jogado para o dia seguinte.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('CREATE EXTENSION IF NOT EXISTS btree_gist');

        DB::statement(<<<'SQL'
            ALTER TABLE reservas ADD CONSTRAINT reservas_sem_sobreposicao
            EXCLUDE USING gist (
                quadra_id WITH =,
                tsrange(
                    data + hora_inicio,
                    data + hora_fim + CASE WHEN hora_fim <= hora_inicio THEN interval '1 day' ELSE interval '0' END
                ) WITH &&
            ) WHERE (status <> 'cancelada')
        SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE reservas DROP CONSTRAINT IF EXISTS reservas_sem_sobreposicao');
    }
};
