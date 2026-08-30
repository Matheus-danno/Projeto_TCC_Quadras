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
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->string('cliente_nome')->nullable()->after('user_id');
            $table->string('cliente_telefone')->nullable()->after('cliente_nome');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropColumn(['cliente_nome', 'cliente_telefone']);
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }
};
