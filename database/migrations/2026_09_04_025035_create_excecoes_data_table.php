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
        Schema::create('excecoes_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dono_id')->constrained('users')->cascadeOnDelete();
            $table->date('data');
            $table->string('descricao');
            $table->boolean('fechado_dia_todo')->default(true);
            $table->time('hora_abertura')->nullable();
            $table->time('hora_fechamento')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('excecoes_data');
    }
};
