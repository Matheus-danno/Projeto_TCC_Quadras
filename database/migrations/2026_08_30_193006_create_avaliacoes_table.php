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
        Schema::create('avaliacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('avaliado_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('autor_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('nota');
            $table->text('comentario')->nullable();
            $table->timestamps();

            $table->unique(['avaliado_id', 'autor_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('avaliacoes');
    }
};
