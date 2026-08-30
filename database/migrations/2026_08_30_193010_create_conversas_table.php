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
        Schema::create('conversas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quadra_id')->constrained('quadras')->cascadeOnDelete();
            $table->foreignId('jogador_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['quadra_id', 'jogador_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversas');
    }
};
