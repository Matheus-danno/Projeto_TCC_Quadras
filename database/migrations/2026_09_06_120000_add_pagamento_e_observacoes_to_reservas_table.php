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
            $table->string('cliente_email')->nullable()->after('cliente_telefone');
            $table->decimal('valor', 8, 2)->nullable();
            $table->text('observacoes')->nullable();
            $table->string('status_pagamento')->default('pendente');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropColumn(['cliente_email', 'valor', 'observacoes', 'status_pagamento']);
        });
    }
};
