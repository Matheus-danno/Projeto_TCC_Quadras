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
        Schema::create('quadras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dono_id')->constrained('users')->cascadeOnDelete();
            $table->string('nome');
            $table->string('endereco');
            $table->string('cidade');
            $table->string('bairro');
            $table->string('esporte');
            $table->decimal('valor_hora', 8, 2);
            $table->boolean('cobertura')->default(false);
            $table->text('descricao')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quadras');
    }
};
