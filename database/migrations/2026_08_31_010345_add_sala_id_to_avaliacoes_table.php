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
        Schema::table('avaliacoes', function (Blueprint $table) {
            $table->dropUnique(['avaliado_id', 'autor_id']);

            $table->foreignId('sala_id')->after('id')->constrained('salas')->cascadeOnDelete();

            $table->unique(['sala_id', 'avaliado_id', 'autor_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('avaliacoes', function (Blueprint $table) {
            $table->dropUnique(['sala_id', 'avaliado_id', 'autor_id']);
            $table->dropConstrainedForeignId('sala_id');

            $table->unique(['avaliado_id', 'autor_id']);
        });
    }
};
