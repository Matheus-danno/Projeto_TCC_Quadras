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
        Schema::table('reservas', function (Blueprint $table) {
            // Snapshot congelado no momento em que a reserva passa a contar
            // comissão (confirmada + paga). Mudanças futuras na taxa, ou no
            // valor/hora da quadra, não afetam reservas já computadas.
            $table->decimal('comissao_percentual', 5, 2)->nullable()->after('valor');
            $table->unsignedBigInteger('valor_total_centavos')->nullable()->after('comissao_percentual');
            $table->unsignedBigInteger('comissao_valor_centavos')->nullable()->after('valor_total_centavos');
            $table->unsignedBigInteger('valor_proprietario_centavos')->nullable()->after('comissao_valor_centavos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropColumn([
                'comissao_percentual',
                'valor_total_centavos',
                'comissao_valor_centavos',
                'valor_proprietario_centavos',
            ]);
        });
    }
};
