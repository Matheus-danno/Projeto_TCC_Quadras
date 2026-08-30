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
        Schema::table('participacao_salas', function (Blueprint $table) {
            $table->string('forma_pagamento')->nullable()->after('user_id');
            $table->decimal('valor_pago', 10, 2)->nullable()->after('forma_pagamento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participacao_salas', function (Blueprint $table) {
            $table->dropColumn(['forma_pagamento', 'valor_pago']);
        });
    }
};
