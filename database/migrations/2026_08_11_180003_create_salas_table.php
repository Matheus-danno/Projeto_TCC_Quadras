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
        Schema::create('salas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quadra_id')->nullable()->constrained('quadras')->nullOnDelete();
            $table->foreignId('criador_id')->constrained('users')->cascadeOnDelete();
            $table->string('nome');
            $table->string('esporte');
            $table->unsignedInteger('max_participantes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salas');
    }
};
