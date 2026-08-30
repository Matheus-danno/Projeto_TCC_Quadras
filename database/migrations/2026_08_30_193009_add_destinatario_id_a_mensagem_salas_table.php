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
        Schema::table('mensagem_salas', function (Blueprint $table) {
            $table->foreignId('destinatario_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mensagem_salas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('destinatario_id');
        });
    }
};
