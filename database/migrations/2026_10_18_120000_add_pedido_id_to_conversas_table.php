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
        Schema::table('conversas', function (Blueprint $table) {
            $table->foreignId('pedido_id')->nullable()->after('quadra_id')->constrained('pedidos')->cascadeOnDelete();

            $table->unique(['pedido_id', 'jogador_id']);
        });

        Schema::table('conversas', function (Blueprint $table) {
            $table->foreignId('quadra_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversas', function (Blueprint $table) {
            $table->dropUnique(['pedido_id', 'jogador_id']);
            $table->dropConstrainedForeignId('pedido_id');
            $table->foreignId('quadra_id')->nullable(false)->change();
        });
    }
};
